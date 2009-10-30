#include <iostream>
#include <cstdio>
#include <mysql.h>

using namespace std;

const char *host = "home.luizribeiro.org";
const char *user = "smaco";
const char *pwd = "senha";
const char *database = "smaco";
MYSQL mysql;

MYSQL* connect (){
	mysql_init(&mysql);
	// connection = 
	// mysql_real_connect(&mysql,"host","user","password","database",0,0,0);
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

int main(void){
	MYSQL * conn = connect();
	if(conn == NULL){
		printf("Couldn't connect\n");
		return 0;
	}
	MYSQL_RES * res = get_ids(conn);
	if(res == NULL){
		printf("Empty query result\n");
		return 0;
	}
	MYSQL_ROW row;
	while((row = mysql_fetch_row(res)) != NULL){
		printf("%s\n",row[0]);
	}
	return 0;
}
