#include <cstdio>
#include <cstdlib>
#include <cstring>
#include <curl/curl.h>
#include <curl/types.h>
#include <curl/easy.h>
#include <mysql.h>
#include <string>
#include <unistd.h>

using namespace std;

/* MYSQL {{{ */
const char *host = "home.luizribeiro.org";
const char *user = "smaco";
const char *pwd = "senha";
const char *database = "smaco";
MYSQL mysql;

MYSQL* connect (){
	mysql_init(&mysql);
	return mysql_real_connect(&mysql, host, user, pwd, database, 0, 0, 0);
}

MYSQL_RES* query(MYSQL * conn, const char *q){
	int q_st;
	q_st = mysql_query(conn, q);
	MYSQL_RES * r;
	if(q_st) return r;
	r = mysql_store_result(conn);
	return r;
}

MYSQL_RES* get_ids(MYSQL * conn){
	return query(conn, "SELECT id FROM userids WHERE EXISTS(\
		SELECT * FROM users WHERE EXISTS(\
		SELECT * FROM participates WHERE EXISTS(\
		SELECT * FROM running_contests\
		WHERE running_contests.contestid = participates.contestid\
		AND participates.uid = users.uid\
		AND users.uid = userids.uid)))");
}
/* }}} */

/* PARSER {{{ */

#define CURL_STATICLIB
#define IN getc( stdin )

bool spec[128];
char line[2048];
char part[256];
FILE *stt;
const char *url[2] = {
	"http://acmicpc-live-archive.uva.es/nuevoportal/status.php",
	"http://uva.onlinejudge.org/"
};

/* {{{ Live Archive Parser */
void parseLA(){
	bool y[15] = {0, 1, 0, 1, 0, 1, 1, 0, 0, 1, 0, 0, 0, 1, 0};
	int ic;
	for(int k = 0; k < 28; ++k) fgets(line, 2048, stdin);
	register char c;
	for(int i = 0; i < 25; ++i){
		ic = 0;
		for(int cnt = 0; cnt < 15; ++cnt){
			while(IN != '<');
			while(IN != '>');
			char *p = part;
			if(y[cnt]) {
				for(c = IN; c != EOF && c != '<'; c = IN){
					if(c == '&') {
						while(IN != ';');
						c = IN;
					}
					if(spec[c]) continue;
					*p++ = c;
					*p = 0;
				}
				if(ic == 2){
					if(part[0] == 'A') strcpy(part,"AC");
					else if(part[0] == 'W') strcpy(part, "WA");
					else if(part[0] == 'T') strcpy(part, "TL");
					else if(part[0] == 'R'){
						if(part[11] != 'F') strcpy(part, "RE");
						else strcpy(part,"RF");
					} else if(part[0] == 'C') strcpy(part, "CE");
					else if(part[0] == 'P') strcpy(part, "PE");
					else if(part[0] == 'M') strcpy(part, "ML");
					else if(part[0] == 'O') strcpy(part, "OL");
				}
				if(ic) fputc('\t', stt);
				fprintf(stt, "%s", part);
				ic++;
			} else while(IN != '<');
			ungetc('<', stdin);
		}
		fputc(10, stt);
	}
}
/* }}} */

/* UVa Parser {{{ */
void parseUVA(){

}
/* }}} */

size_t write_data(void *ptr, size_t size, size_t nmemb, FILE *stream) {
	size_t written;
	written = fwrite(ptr, size, nmemb, stream);
	return written;
}

void parse(int judge){
	CURL *curl;
	FILE *fp;
	CURLcode res;
	curl = curl_easy_init();
	if (curl) {
		fp = fopen("status","wb");
		curl_easy_setopt(curl, CURLOPT_URL, url[judge]);
		curl_easy_setopt(curl, CURLOPT_WRITEFUNCTION, write_data);
		curl_easy_setopt(curl, CURLOPT_WRITEDATA, fp);
		res = curl_easy_perform(curl);
		curl_easy_cleanup(curl);
		fclose(fp);
	}
	freopen("status", "r", stdin);
	stt = fopen("parsed.txt","wb");
	switch(judge){
		case 0: parseLA(); break;
		case 1: parseUVA(); break;
	}
	fclose(stt);
}
void init_parser(){
	for(int i = 0; i < 128; ++i) spec[i] = 0;
	spec['\t'] = spec['\n'] = 1;
}

/* }}} */

/* UPDATE {{{ */

void update(int judge){

}

/* }}} */

int main(void){
	MYSQL * conn = connect();
	if(conn == NULL){
		printf("FATAL: Couldn't connect\n");
		return 0;
	}
	init_parser();
	while(1){
		/* 5 segundos sem fazer nada */
		sleep(5);
		/* Verifica se algum contest esta rodando */
		MYSQL_RES * res = NULL;// = query(conn, "SELECT judgeid FROM running_contests");
		if(res == NULL){
			printf("No contests running\n");
			continue;
		}
		/* Tem pelo menos um contest rodando */
		MYSQL_ROW r;
		while((r = mysql_fetch_row(res)) != NULL){
			/* Pega o judgeid */
			int j_id = atoi(r[0]);
			/* Roda o parser */
			parse(j_id);
			/* Interpreta a saida do parser
			   e atualiza runs
			 */
			update(j_id);
		}
	}
	return 0;
}

