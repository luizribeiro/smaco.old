#include <cstdio>
#include <cstring>

using namespace std;

#define IN getc( stdin )

char line[2048];
char part[256];
bool y[15] = {0, 1, 0, 1, 0, 1, 0, 0, 0, 1, 0, 0, 0, 1, 0};
bool spec[128];
int ic;

int main(void){
	for(int i = 0; i < 128; ++i) spec[i] = 0;
	for(int k = 0; k < 28; ++k) fgets(line, 2048, stdin);
	spec['\t'] = spec['\n'] = 1;
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
				printf("%s\t", part);
				ic++;
			} else while(IN != '<');
			ungetc('<', stdin);
		}
		putc(10, stdout);

	}
	return 0;
}

