# src=./src/Agent.cpp ./src/CpuExec.cpp ./src/MemoryExec.cpp ./src/Base.cpp ./src/Log.cpp ./src/CMsgQueue.cpp ./src/ILock.cpp ./src/Procs.cpp ./src/Swap.cpp ./src/IO.cpp ./src/jsoncpp.cpp
src=./src/Agent.cpp ./src/Nature.cpp ./src/Base.cpp ./src/Log.cpp ./src/CMsgQueue.cpp ./src/ILock.cpp ./src/jsoncpp.cpp -O2 -DNDEBUG


tagent : ./src/Agent.cpp
	# g++ -o ./bin/tagent $(src)  -I /usr/local/tagent/include/ -L /usr/local/tagent/lib/ -lpthread -lrdkafka -lboost_regex
	g++ -o ./bin/tagent $(src)  -I /usr/local/tagent/include/ -L /usr/local/tagent/lib/ -lpthread -lrdkafka -lboost_regex

.PHONY : clean
clean:
	rm -rf ./bin/*

.PHONY : cleanall
cleanall:
	rm -rf ./bin/* && rm -rf ./log/* && rm -rf ./data/Agent.data* && rm -rf ./nohup.out

.PHONY: output
output :
	make
	mkdir ./output
	cp -r ./item_conf ./output/item_conf
	cp -r ./data ./output/data
	cp -r ./logs ./output/logs
	cp -r ./lib ./output/lib
	cp -r ./bin ./output/bin
	cp -r ./include ./output/include
	cp ./load_agent.sh ./output/load_agent.sh
	chmod u+x ./output/load_agent.sh
	chown root:work -R output	
        
