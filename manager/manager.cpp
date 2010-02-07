#include <algorithm>
#include <cstdio>
#include <cstdlib>
#include <cstring>
#include <curl/curl.h>
#include <curl/types.h>
#include <curl/easy.h>
#include <map>
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

MYSQL_RES* query(const char *q){
	int q_st = mysql_query(conn, q);
	MYSQL_RES * r;
	if(q_st) return NULL;
	r = mysql_store_result(conn);
	return r;
}

bool connect(){
	mysql_init(&mysql);
	conn = mysql_real_connect(&mysql, host, user, pwd, database, 0, 0, 0);
	if(conn != NULL) query("SET SESSION time_zone = '+0:00';");
	return conn != NULL;
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

MYSQL_RES* get_ids(int jid, int cid){
	string q = string("SELECT id FROM userids WHERE EXISTS(") +
				string("SELECT uid FROM users WHERE EXISTS(") +
				string("SELECT uid FROM participates WHERE EXISTS(") +
				string("SELECT contestid FROM running_contests AS rc") +
				string(" WHERE rc.contestid = participates.contestid") +
				string(" AND participates.uid = users.uid AND") +
				string(" users.uid = userids.uid AND userids.judgeid = ") +
				itos(jid) + string(" AND rc.contestid = ") + itos(cid) + 
				string(")))");
	return query(q.c_str());
}

MYSQL_RES* get_probs(string cid, string jid){
	string p = string("SELECT problemid FROM problems, running_contests WHERE ") +
				string("problems.contestid = running_contests.contestid AND ") +
				string("problems.contestid = ") + cid + 
				string(" AND running_contests.judgeid = ") + jid;
	return query(p.c_str());
}

int get_judgeid(int cid){
	string c = "select judgeid from running_contests where contestid = " + 
				itos(cid);
	MYSQL_RES * jnum_q = query(c.c_str());
	MYSQL_ROW jnum_r = NR(jnum_q);
	return atoi(jnum_r[0]);
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

/* PARSER {{{1 */

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
	for(char *p = part; *p; ++p) *p = tolower(*p);
	if(!strcmp(part,"accepted"))				 	strcpy(part, "AC");
	else if(!strcmp(part, "wrong answer"))		 	strcpy(part, "WA");
	else if(!strcmp(part, "time limit exceeded")) 	strcpy(part, "TL");
	else if(!strcmp(part, "runtime error"))			strcpy(part, "RE");
	else if(!strcmp(part, "presentation error"))	strcpy(part, "PE");
	else if(!strcmp(part, "compile error"))			strcpy(part, "CE");
	else if(!strcmp(part, "compilation error"))	 	strcpy(part, "CE");
	else if(!strcmp(part, "restricted function")) 	strcpy(part, "RF");
	else if(!strcmp(part, "memory limit exceeded"))	strcpy(part, "ML");
	else if(!strcmp(part, "output limit exceeded"))	strcpy(part, "OL");
	else strcpy(part, "IG");
}

/* Live Archive Parser {{{2 */
void parseLA(){
	bool y[15] = {0, 1, 0, 1, 0, 1, 1, 0, 0, 1, 0, 1, 0, 1, 0};
	int ic;
	for(int k = 0; k < 28; ++k) fgets(line, 2048, stdin);
	register char c;
	for(int i = 0; i < 25; ++i){
		ic = 0;
		for(int cnt = 0; cnt < 15; ++cnt){
			while(IN != '<');
			if(!cnt && (c = IN) == '/') goto out;
			else ungetc(c, stdin);
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
out:
	while(fgets(line, 2048, stdin));
}
/* }}}2 */

/* UVa Parser {{{2 */
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
/* }}}2 */

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
	printf("Parsing...");
	switch(judge){
		case 0: parseLA(); break;
		case 1: parseUVA(); break;
	}
	printf("DONE\n");
	fclose(stt);
}
void init_parser(){
	for(int i = 0; i < 128; ++i) spec[i] = 0;
	spec['\t'] = spec['\n'] = 1;
}

/* }}} */

/* UPDATE {{{1 */

const char *judge_name[2] = {"Live Archive", "UVa"};
int pid, sid, uid;
char ans[8], day[16], hour[16], lang[8], runtime[16];
set < int > s, u;
string jla = string("0"), juva = string("1");

void insert_ids(int jid, int cid){
	u.clear();
	MYSQL_RES * ids = get_ids(jid, cid);
	if(ids == NULL) {
		printf("FAIL: Getting contestants id's.\n");
		return;
	}
	MYSQL_ROW id = NR(ids);
	if(id != NULL){
		printf("Contestants: [");
		while(id != NULL){
			printf(" %d",atoi(id[0]));
			u.insert(atoi(id[0]));
			id = NR(ids);
			if(id != NULL) putc(',', stdout);
			else putc(32, stdout);
		}
		printf("]\n");
	} else printf("No contestants.\n");
	mysql_free_result(ids);
}

void insert_probs(int jid, int cid){
	s.clear();
	MYSQL_RES * probs = get_probs(itos(cid), itos(jid));
	if(probs == NULL) {
		printf("FAIL: Selecting problems\n");
		return;
	}
	MYSQL_ROW r = NR(probs);
	if(r != NULL){
		printf("Problems [");
		while(r != NULL){
			printf(" %d", atoi(r[0]));
			s.insert(atoi(r[0]));
			r = NR(probs);
			if(r != NULL) putc(',', stdout);
			else putc(32, stdout);
		}
		printf("]\n");
	} else printf("No problems.\n");
	mysql_free_result(probs);
}

void insert_run(string jid, string cid){
	if(!strcmp(ans,"IG") || s.find(pid) == s.end() || u.find(uid) == u.end())
		return;
	printf("Inserting run %d by %d, prob %d (%s)\n", sid, uid, pid, ans);
	int pn = get_pid(itos(pid), cid);
	int un = get_uid(itos(uid), jid);
	/* Insere a submissao no BD */
	string cmd = string("insert into runs (runid, judgeid, uid, ") +
				 string("pid, date, answer, runtime, language) VALUES (")
				 + itos(sid) + ", " + jid  + ", " +  itos(un) + ", " + 
				 itos(pn) + ", '" + string(day) + " " + string(hour) +
				 "', '" + string(ans) + "', " + string(runtime) 
				 + ", '" + string(lang) + "');";
#ifdef DEBUG_INSERT_RUN
	printf("Command (%s)\n", cmd.c_str());
#endif
	query(cmd.c_str());
}
/* Live Archive update RUNS {{{2 */

void updateLA(int cid){
	string _cid = itos(cid);
	while(scanf("%d %s %s %s %s %d %s %d", &sid, day, hour, ans,
			runtime, &uid, lang, &pid) != EOF){
		insert_run(jla, _cid);
	}
}

/* }}}2 */

/* UVa update RUNS {{{2 */

void updateUVa(int cid){
	string _cid = itos(cid);
	while(scanf("%d %d %d %s %s %s %s %s", &sid, &pid, &uid, ans,
			lang, runtime, day, hour) != EOF){
		insert_run(juva, _cid);
	}
}

/* }}}2 */

void update(int judge, int cid){
	printf("[%s]\n", judge_name[judge]);
	insert_ids(judge, cid);
	insert_probs(judge, cid);
	freopen("parsed.txt", "r", stdin);
	switch(judge){
		case 0: updateLA(cid); break;
		case 1: updateUVa(cid); break;
	}

}

/* }}}1 */

/* SCORE {{{ */

void score(int jid, int cid){
	/* Mapeia um id para 0 <= x <= N -> numero de participantes */
	map < int , int > m;
	int uc = 0;
	string q;
}

/* }}} */

/* main {{{ */

set < int > was, is;

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
		if(r == NULL) printf("No running contests.\n");
		/* Tem pelo menos um contest rodando */
		is.clear();
		while(r != NULL){
			printf("\n~~~~~~~~~~ \"%s\" ~~~~~~~~~~\n\n", r[1]);
			/* Pega o judgeid e o contestid */
			int j_id = atoi(r[0]), c_id = atoi(r[2]);
			is.insert(c_id);
			/* Roda o parser */
			parse(j_id);
			/* Interpreta a saida do parser e atualiza runs */
			update(j_id, c_id);
			r = NR(res);
		}
		for(set < int > :: iterator it = was.begin(); it != was.end(); it++)
			if(is.find(*it) == is.end()){
				printf("Contest %d just ended\n", *it);
				int judge = get_judgeid(*it); 
				score(judge, *it);
			}
		was.clear();
		for(set < int > :: iterator it = is.begin(); it != is.end(); it++)
			was.insert(*it);
		/* Libera a memoria utilizada pela query */
		mysql_free_result(res);
	}
	return 0;
}

/* }}} */

