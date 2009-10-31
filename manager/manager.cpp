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

const char *host = "home.luizribeiro.org";
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

MYSQL_RES* get_probs(string cid, string jid){
	string p = string("SELECT problemid FROM problems, running_contests WHERE ") +
				string("problems.contestid = running_contests.contestid AND ") +
				string("problems.contestid = ") + cid + 
				string(" AND running_contests.judgeid = ") + jid;
	return query(p.c_str());
}

int get_uid(string uid, string judgeid){
	string c = "select uid from userids where id = " + uid +
				" AND judgeid = " + judgeid;
	MYSQL_RES * unum_q = query(c.c_str());
	MYSQL_ROW unum_r = NR(unum_q);
	return atoi(unum_r[0]);
}

int get_pid(string pid, string cid){
	string c = "select pid from problems where problemid = " + pid + 
				" AND contestid = " + cid;
	MYSQL_RES * pnum_q = query(c.c_str());
	MYSQL_ROW pnum_r = NR(pnum_q);
	return atoi(pnum_r[0]);
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
	"http://uva.onlinejudge.org/index.php?option=onlinejudge&Itemid=19"
};

void adapt(){
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

/* {{{ Live Archive Parser */
void parseLA(){
	bool y[15] = {0, 1, 0, 1, 0, 1, 1, 0, 0, 1, 0, 1, 0, 1, 0};
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
				}
				*p = 0;
				if(ic == 2) adapt();
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
	bool y[25];
	for(int i = 0; i < 25; ++i) y[i] = 0;
	y[1] = y[4] = y[15] = y[17] = y[19] = y[22] = 1;
	for(int i = 0; i < 230; ++i) fgets(line, 2048, stdin);
	int ic, k;
	register char c;
	for(int i = 0; i < 50; ++i){
		ic = 0;
		for(int cnt = 0; cnt < 25; ++cnt){
			char *p = part;
			while(IN != '<');
			if(cnt != 12) {
				for(c = IN, k = 1; ; c = IN){
					k += (c == '<');
					k -= (c == '>');
					if(!k) break;
				}
			} else {
				for(c = IN; !isdigit(c); c = IN);
				while(isdigit(c)) *p++ = c, c = IN;
				*p = 0;
				fprintf(stt, "\t%5s", part);
			}
			if(y[cnt]) {
				for(c = IN; c != EOF && c != '<'; c = IN){
					if(c == '&') {
						while(IN != ';');
						c = IN;
					}
					if(spec[c]) continue;
					*p++ = c;
				}
				*p = 0;
				if(ic == 2) adapt();
				if(ic == 3)
					if(!strcmp(part, "ANSI C")) strcpy(part,"C");
				if(ic) fputc('\t', stt);
				fprintf(stt, "%5s", part);
				ic++;
			} else while(IN != '<');
			ungetc('<', stdin);
		}
		fputc(10, stt);
	}
	while(fgets(line, 2048, stdin));
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
	int pid, sid, uid;
	char ans[8], day[16], hour[16], lang[8], runtime[16];
	/* Seleciona os problemas desse contest, nesse judge */
	MYSQL_RES * probs = get_probs(itos(cid), string("0"));
	if(probs == NULL) {
		printf("FAIL: Selecting problems\n");
		return;
	}
	s.clear();
	for(MYSQL_ROW r = NR(probs); r != NULL; r = NR(probs)){
		printf("--> Problem %d\n", atoi(r[0]));
		s.insert(atoi(r[0]));
	}
	while(scanf("%d %s %s %s %s %d %s %d", &sid, day, hour, ans,
			runtime, &uid, lang, &pid) != EOF){
		if(s.find(pid) == s.end() || u.find(uid) == u.end()) continue;
		printf("Inserting submission %d by %d, problem %d\n", sid, uid, pid);
		int pn = get_pid(itos(pid), itos(cid));
		int un = get_uid(itos(uid), string("0"));
		/* Insere a submissao no BD */
		string cmd = string("insert into runs (runid, judgeid, uid, ") +
					 string("pid, date, answer, runtime, language) VALUES (")
					 + itos(sid) + ", 0, " +  itos(un) + ", " + 
					 itos(pn) + ", '" + string(day) + " " + string(hour) +
					 "', '" + string(ans) + "', " + string(runtime) 
					 + ", '" + string(lang) + "');";
		printf("Command (%s)\n", cmd.c_str());
		query(cmd.c_str());
	}
}

/* }}} */

/* UVa update RUNS {{{ */

void updateUVa(int cid){
	int pid, sid, uid;
	char ans[8], day[16], hour[16], lang[8], runtime[16];
	/* Seleciona os problemas desse contest, nesse judge */
	MYSQL_RES * probs = get_probs(itos(cid), string("1"));
	if(probs == NULL) {
		printf("FAIL: Selecting problems\n");
		return;
	}
	s.clear();
	for(MYSQL_ROW r = NR(probs); r != NULL; r = NR(probs)){
		printf("--> Problem %d\n", atoi(r[0]));
		s.insert(atoi(r[0]));
	}
	while(scanf("%d %d %d %s %s %s %s %s", &sid, &pid, &uid, ans,
			lang, runtime, day, hour) != EOF){
		if(s.find(pid) == s.end() || u.find(uid) == u.end()) continue;
		printf("Inserting submission %d by %d, problem %d\n", sid, uid, pid);
		int pn = get_pid(itos(pid), itos(cid));
		int un = get_uid(itos(uid), string("1"));
		/* Insere a submissao no BD */
		string cmd = string("insert into runs (runid, judgeid, uid, ") +
					 string("pid, date, answer, runtime, language) VALUES (")
					 + itos(sid) + ", 1, " +  itos(un) + ", " + 
					 itos(pn) + ", '" + string(day) + " " + string(hour) +
					 "', '" + string(ans) + "', " + string(runtime) 
					 + ", '" + string(lang) + "');";
		printf("Command (%s)\n", cmd.c_str());
		query(cmd.c_str());
	}
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
	freopen("parsed.txt", "r", stdin);
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

