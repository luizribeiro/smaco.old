#include <algorithm>
#include <cstdio>
#include <cstdlib>
#include <cstring>
#include <curl/curl.h>
#include <curl/types.h>
#include <curl/easy.h>
#include <mysql.h>
#include <set>
#include <string>
#include <unistd.h>

using namespace std;

/* MYSQL {{{ */

#define NR(x) mysql_fetch_row(x)

const char *host = "localhost";
const char *user = "smaco";
const char *pwd = "senha";
const char *database = "smaco";
MYSQL mysql, *conn;

bool connect(){
	mysql_init(&mysql);
	conn = mysql_real_connect(&mysql, host, user, pwd, database, 0, 0, 0);
	return conn != NULL;
}

MYSQL_RES* query(const char *q){
	int q_st = mysql_query(conn, q);
	MYSQL_RES * r;
	if(q_st) return NULL;
	r = mysql_store_result(conn);
	return r;
}

string itos(int k){
	string s;
	do {
		s += (char)(k % 10 + '0');
		k /= 10;
	} while(k);
	reverse(s.begin(), s.end());
	return s;
}

MYSQL_RES* get_ids(int jid){
	string q = "SELECT id FROM userids WHERE EXISTS(\
		SELECT uid FROM users WHERE EXISTS(\
		SELECT uid FROM participates WHERE EXISTS(\
		SELECT contestid FROM running_contests\
		WHERE running_contests.contestid = participates.contestid\
		AND participates.uid = users.uid\
		AND users.uid = userids.uid\
		AND userids.judgeid = " + itos(jid) + ")))";
	return query(q.c_str());
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
	while(fgets(line, 2048, stdin));
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
	printf("Parsing\n");
	switch(judge){
		case 0: parseLA(); break;
		case 1: parseUVA(); break;
	}
	printf("Parsed\n");
	fclose(stt);
}
void init_parser(){
	for(int i = 0; i < 128; ++i) spec[i] = 0;
	spec['\t'] = spec['\n'] = 1;
}

/* }}} */

/* UPDATE {{{ */

const char *judge_name[2] = {"Live Archive", "UVa"};
set < int > s, u;

/* Live Archive update RUNS {{{ */

void updateLA(int cid){
	/* Seleciona os problemas desse contest, nesse judge */
	string jid = "0", p;
	p = string("SELECT problemid FROM problems, running_contests WHERE ") +
		string("problems.contestid = running_contests.contestid AND ") +
		string("problems.contestid = ") + itos(cid) + 
		string(" AND running_contests.judgeid = ") + jid;
	//printf("Query (%s)\n",p.c_str());
	int pid, sid, uid;
	char day[16], hour[16], ans[8], runtime[16];
	MYSQL_RES * probs = query(p.c_str());
	if(probs == NULL) {
		printf("No problems\n");
		return;
	}
	s.clear();
	for(MYSQL_ROW r = NR(probs); r != NULL; r = NR(probs)){
		printf("--> Problem %d\n", atoi(r[0]));
		s.insert(atoi(r[0]));
	}
	freopen("parsed.txt", "r", stdin);
	while(scanf("%d %s %s %s %s %d %d", &sid, day, hour, ans,
			runtime, &uid, &pid) != EOF){
		if(s.find(pid) == s.end() || u.find(uid) == u.end()) continue;
		/* Insere o problema */
		printf("Inserting submission %d by %d, problem %d\n", sid, uid, pid);
		string c1 = "select pid from problems where problemid = " + itos(pid) + 
					" AND contestid = " + itos(cid);
		MYSQL_RES * pnum_q = query(c1.c_str());
		MYSQL_ROW pnum_r = NR(pnum_q);
		int pn = atoi(pnum_r[0]);
		string c2 = "select uid from userids where id = " + itos(uid);
		MYSQL_RES * unum_q = query(c2.c_str());
		MYSQL_ROW unum_r = NR(unum_q);
		int un = atoi(unum_r[0]);
		string cmd = string("insert into runs (runid, judgeid, uid, ") +
					 string("pid, date, answer, runtime, language) VALUES (")
					 + itos(sid) + ", " + jid + ", " +  itos(un) + ", " + 
					 itos(pn) + ", '" + string(day) + " " + string(hour) +
					 "', '" + string(ans) + "', " + string(runtime) 
					 + ", 'C++');";
		printf("Command (%s)\n", cmd.c_str());
		query(cmd.c_str());
	}

}

/* }}} */

/* UVa update RUNS {{{ */

void updateUVa(int cid){

}

/* }}} */

void update(int judge, int cid){
	u.clear();
	printf("[%s]\n", judge_name[judge]);
	/* Pega os ids que estao participando de algum contest
	** nesse judge.
	*/
	MYSQL_RES * ids = get_ids(judge);
	if(ids == NULL) printf("FAIL.\n");
	MYSQL_ROW id = NR(ids);
	if(id != NULL) while(id != NULL){
		printf("--> User %d\n",atoi(id[0]));
		u.insert(atoi(id[0]));
		id = NR(ids);
	} else printf("No participants.\n");
	mysql_free_result(ids);
	printf("--------------------\n");
	switch(judge){
		case 0: updateLA(cid); break;
		case 1: updateUVa(cid); break;
	}

}

/* }}} */

int main(void){
	if(!connect()){
		printf("FATAL: Couldn't connect\n");
		return 0;
	}
	init_parser();
	while(1){
		/* 5 segundos sem fazer nada */
		sleep(5);
		/* Verifica se algum contest esta rodando */
		MYSQL_RES * res = query("SELECT judgeid, nome, contestid FROM running_contests");
		if(res == NULL){
			printf("FAIL: selecting from running_contests\n");
			continue;
		}
		MYSQL_ROW r = NR(res);
		if(r == NULL){
			printf("No running contests.\n");
			continue;
		}
		/* Tem pelo menos um contest rodando */
		while(r != NULL){
			printf("Contest (%s)\n", r[1]);
			/* Pega o judgeid e o contestid */
			int j_id = atoi(r[0]), c_id = atoi(r[2]);
			/* Roda o parser */
			parse(j_id);
			/* Interpreta a saida do parser
			   e atualiza runs
			 */
			update(j_id, c_id);
			r = NR(res);
		}
		/* Libera a memoria utilizada pela query */
		mysql_free_result(res);
	}
	return 0;
}

