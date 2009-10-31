all: manager

manager: manager.cpp
	g++ manager.cpp -lcurl -lmysqlclient -L /usr/include/mysql/ -I /usr/include/mysql/ -o manager

clean:
	rm manager status parsed.txt
