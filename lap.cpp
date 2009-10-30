#include <cstdio>
#include <cstring>
#include <curl/curl.h>
#include <curl/types.h>
#include <curl/easy.h>
#include <string>

using namespace std;

#define CURL_STATICLIB
#define DEBUG
#define IN getc( stdin )

bool y[15] = {0, 1, 0, 1, 0, 1, 1, 0, 0, 1, 0, 0, 0, 1, 0};
bool spec[128];
char line[2048];
char part[256];
const char *url = "http://acmicpc-live-archive.uva.es/nuevoportal/status.php";
const char *out = "status";
int ic;
FILE *stt;

size_t write_data(void *ptr, size_t size, size_t nmemb, FILE *stream) {
	size_t written;
	written = fwrite(ptr, size, nmemb, stream);
	return written;
}
/* {{{ Live Archive Parser */
void parseLA(){
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
int main(void){
	for(int i = 0; i < 128; ++i) spec[i] = 0;
	spec['\t'] = spec['\n'] = 1;
	CURL *curl;
	FILE *fp;
	CURLcode res;
	curl = curl_easy_init();
	if (curl) {
		fp = fopen(out,"wb");
		curl_easy_setopt(curl, CURLOPT_URL, url);
		curl_easy_setopt(curl, CURLOPT_WRITEFUNCTION, write_data);
		curl_easy_setopt(curl, CURLOPT_WRITEDATA, fp);
		res = curl_easy_perform(curl);
		curl_easy_cleanup(curl);
		fclose(fp);
	}
	freopen(out, "r", stdin);
	stt = fopen("parsed.txt","wb");
	parseLA();
	fclose(stt);
	return 0;
}

