#include <iostream>
#include <fstream>
#include <ctime>
#include <string>
#include <cstdio>
#include <string.h>
#include <cassert>
#include <pthread.h>
#include <unistd.h>
#include <ctime>
#include <stdlib.h>
#include <errno.h>
#include <sys/unistd.h>
#include <sys/inotify.h>
#include <sys/types.h>
#include <regex.h>

#include "Global.h"
#include "Nature.h"
#include "Base.h"
#include "Log.h"
#include "CMsgQueue.h"
#include "ConfExec.h"
#include "Proc.h"
#include "Port.h"

#include "Business.h"
#include "json/json.h"

/* 
*    日志采集 
*   根据传入的配置文件名称查找指定的日志文件，读取配置，创建线程
*   @param1 配置文件名称
*/
void* threadLog(void* arg) {
    pthread_detach(pthread_self());
    threadArg* args      = (threadArg*) arg;
    std::string logPath  = args->_logPath;
    std::string style    = args->_style;
    std::string format   = args->_format;        
    std::cout << "new thread..." << std::endl;

    if("newly" == style) {  // 日期风格的日志
        if(format.find("%d") != std::string::npos && format.find("%H") == std::string::npos) {
            readingNewlyStyleLogs(logPath.c_str(), false, false);
        }else if(format.find("%d") != std::string::npos && format.find("%H") != std::string::npos) {
            readingNewlyStyleLogs(logPath.c_str(), true, false);            
        }else {
            return NULL;            
        }
    }else {                 // 分割风格的日志
        readingSplitStyleLogs(logPath.c_str());
    }
    return NULL;
}

/* 进程存在性校验 */
void* threadProcExisted(void* arg) {
    pthread_detach(pthread_self());
    while(true) {
        std::string procInfoStr = procExistedExec();
        g_msgQue->push(procInfoStr);    
        usleep(1000);        
        // sleep(7+SYSINFO_INTERVAL);
    }
}

/* 进程性能校验 (进程数目可能较多, 考虑时间性能开销, 故这个进程生产资源的执行在procPerformanceExec函数内部实现)*/
void* threadProcPerformance(void* arg) {
    pthread_detach(pthread_self());
    while(true) {
        procPerformanceExec();
        // g_msgQue->push(procPerfStr);  // 内部生产
        // sleep(7); // 内部控制sleep
    }
}

/* 端口存在性校验 */
void* threadPort(void* arg) {
    pthread_detach(pthread_self());
    while(true) {
        std::string portInfoStr = portExec();
        g_msgQue->push(portInfoStr);
        usleep(1000);        
        // sleep(7+SYSINFO_INTERVAL);
    }    
}

void* threadHeartBeat(void* arg) {
    pthread_detach(pthread_self());
    while(true) {
        Log log = Log("nature");
        log.append((intToString(getTimeStamp()) + std::string("BUS_Tagent_Heartbeat")).c_str() );
        sleep(10);
    }
}

/* 实时热加载配置 
 * (18.05.22更新) 难维护, 放弃热加载, 每次检测到配置文件有变化, 直接重启
 *
*/

void loadingConf() {
    std::string confPath = g_ROOT_PATH + "item_conf/";   // 要监听的配置文件的路径
    int fd = inotify_init();  
    int wd = inotify_add_watch(fd, confPath.c_str(), IN_CREATE|IN_MODIFY|IN_DELETE);;  
    int len;
    int nread;  
    char buf[1024];  
    buf[sizeof(buf) - 1] = 0; 

    struct inotify_event *event;  

    while( (len = read(fd, buf, sizeof(buf) - 1)) > 0 ) {  
        nread = 0;  
        while( len > 0 ) {  
            event = (struct inotify_event *)&buf[nread];  
            std::string changeFile = event->name;
            if(changeFile.find(".conf") != std::string::npos) {
                char restartBuf[4096];

                if(event->mask & IN_MODIFY) {           // 修改配置文件
                    const std::string cmd = "cd /usr/local/tagent && ./load_agent.sh restart";
                    executeCMD(cmd.c_str(), restartBuf, sizeof(restartBuf));
                    sleep(1);
                    exit(0);
                    // std::cout << "\n\n" << std::endl;
                    // std::cout << "modify" << event->name << std::endl;
                    // deleteConf(changeFile.c_str());
                    // // 创建线程
                    // std::vector<threadArg> argVec = getConf(changeFile.c_str());
		            // usleep(100000);
                    // // std::cout << "debug_Agent.cpp:102" << std::endl;
                    // for(std::vector<threadArg>::iterator it = argVec.begin(); it != argVec.end(); ++it) {
                    //     threadArg arg = *it;
                    //     const std::string& path = it->_logPath;
                    //     pthread_t tLog;
			        //     std::cout << "new listing thread : " << path << std::endl;
		            //     g_businessMap.init(g_confMap);
                    //     pthread_create(&tLog, NULL, threadLog, (void*)&arg);
		            //     usleep(100000);
                    //     g_confThread[path] = tLog;        
                    // }
                    // // 更新业务merge类
                    // g_businessMap.init(g_confMap);
                } else if(event->mask & IN_DELETE) {    // 删除配置文件
                        const std::string cmd = "cd /usr/local/tagent && ./load_agent.sh restart";
                        executeCMD(cmd.c_str(), restartBuf, sizeof(restartBuf));
                        sleep(1);
                        exit(0);
                        // std::cout << "delete" << std::endl;                    
                        // // 获取路径，删除g_confMap，还要根据路径删除相应的pid
                        // deleteConf(changeFile.c_str());    
                        // print_g_confMap();
		                // g_businessMap.init(g_confMap);
                } else if(event->mask & IN_CREATE) {
                        const std::string cmd = "cd /usr/local/tagent && ./load_agent.sh restart";
                        executeCMD(cmd.c_str(), restartBuf, sizeof(restartBuf));
                        sleep(1);
                        exit(0);
                        // std::cout << "create: " << event->name << std::endl;
                        // 不做任何事
                }
            }
            nread = nread + sizeof(struct inotify_event) + event->len;  
            len = len - sizeof(struct inotify_event) - event->len;  
        }
    }

}

/* 热加载线程 */
void* threadLoading(void* arg) {
    loadingConf();
    return NULL;
}

/* 性能数据采集 (CPU、内存、磁盘、网卡信息等) */
void* threadNatureReader(void* arg) {
    pthread_detach(pthread_self());
    while(true) {
        VmStat vmStat = getVmStat();

        Log log = Log("nature");
        // 内存
        std::string memInfoStr = memoryExec();
	    usleep(100);
        // log.append("nature info: memory, ");

        // 交换分区
        std::string swapInfoStr = swapExec(vmStat._si, vmStat._so);
	    usleep(100);
        // log.append("swap, ");

        // 磁盘
        std::string diskInfoStr = DiskTest();
	    usleep(100);
        // std::string diskInfoStr = "";        
        // log.append("disk, ");

        // CPU
        std::string cpuInfoStr = cpuExec(CPU_SAM_INTERVAL, vmStat._in, vmStat._cs);
	    usleep(100);
        // log.append("cpu, ");

        // 进程调度
        std::string procsInfoStr = procsExec(vmStat._r, vmStat._b, vmStat._swpd);
	    usleep(100);
        // log.append("procs, ");

        // 网卡
        std::string netInfoStr = netInfos(2);
    	usleep(100);
        // I/O (I/O相关的监控项放在一个单独的线程中:threadIOReader)
        // ioExec(vmStat._bi, vmStat._bo);
        
        // 拼字符串： 1 + IP + TIMESTAMP + data
        std::string natureStr = memInfoStr + " " + diskInfoStr + " " + cpuInfoStr + " " + procsInfoStr + " " + swapInfoStr + " " + netInfoStr;
        // 生产资源
        g_msgQue->push(natureStr);
        // log.append("finished");

        sleep(SYSINFO_INTERVAL);
    }
}

// 因为采集I/O比较耗时因此单独开启一个线程进行处理
void* threadIOReader(void* arg) {
    pthread_detach(pthread_self());
    while(true) {
        std::string ioInfoStr = ioExec(0, 0); 
        g_msgQue->push(ioInfoStr);
        usleep(1000);
    }    
}

/* 消费者(不断从队列中读取, 并将读到的字符串写入日志文件) */
void* threadDataWriter(void* arg) {
    // static int sleepTime = 5000; // 消费者等待时间
    static int sleepTime = 500000;  // 新规则下不需要间隔那么短了, 因此改为 0.5s 估计 cpu 占用会降低将近一半许多
    static int count = 0;	    // 计数器
    static int dataCount = 0;      // 日志计数器
    static std::string dataName = "";
    dataName = dataName + std::string("data/Agent.data") + std::string(".") + intToString(getNowTime());
    pthread_detach(pthread_self());
    while(true) {
        FILE* fp = fopen(dataName.c_str(), "a+");
	    ++count;
	    ++dataCount;
    	count = count % 1000;
    	if(count == 0) {
    	    std::cout << "MSG QUEUE LENGTH: " << g_msgQue->getSz() << std::endl;
    	} 
        std::string data = g_msgQue->pop();
        if(data.size() > 0) {
            std::string timeStamp = intToString(getTimeStamp());
            std::string frontStr = g_hostName + " " + g_localIp + " " + timeStamp + " ";
            data = frontStr + data;
            char buf[51200];
            strcpy(buf, data.c_str());

            // 向kafka发送
            int ret = rd_kafka_produce(g_rkt, RD_KAFKA_PARTITION_UA, RD_KAFKA_MSG_F_COPY, (void*)buf, (size_t)strlen(buf),NULL, 0,NULL);
            //(调试)输出数据
            // std::cout << buf << std::endl;
            // std::cout << "produce ret =" << ret << std::endl;
            ret = rd_kafka_poll(g_rk, 0/*non-blocking*/);   // 就是说要持续调用这个方法    
            // std::cout << "poll ret =" << ret << std::endl;
            
            rd_kafka_flush(g_rk, 10*1000 /* wait for max 10 seconds */);

            // 本地写一份 (这个就是一期的data文件)
            fprintf(fp, "%s\n", data.c_str()); 
            fflush(fp);
            // 更新主机名
            if(dataCount % 2000 == 0) {
                g_hostName = getHostName();
                std::cout << "[NOTICE] host name = " << g_hostName << std::endl;
            }
	        // 超过200W条记录, 则清空文件
	        if(dataCount >= 2000000) {
            	fclose(fp);
	            fp = fopen(dataName.c_str(), "w+");
	            fclose(fp);
                dataName = std::string("data/Agent.data") + std::string(".") + intToString(getNowTime());
                dataCount = 0;
	        }
        }
        usleep(sleepTime);                           // 时间暂时写死      
        fclose(fp);
    }
}

// DEBUG
// int main() {
//     pthread_t tIO;    
//     pthread_create(&tIO, NULL, threadIOReader, NULL);
//     sleep(10000);
//     return 0;
// }

//正式使用
int main() {
    InitGlobal();
    InitConf();

    pthread_t tNature;    
    pthread_t tDataWriter;
    pthread_t tIO;        
    pthread_create(&tDataWriter, NULL, threadDataWriter, NULL);
    usleep(50000);
    pthread_create(&tNature, NULL, threadNatureReader, NULL);
    usleep(50000);
    pthread_create(&tIO, NULL, threadIOReader, NULL);
    
    // 进程
    pthread_t tProc;
    pthread_create(&tProc, NULL, threadProcExisted, NULL);
    usleep(50000);
    pthread_t tProcPerf;
    pthread_create(&tProcPerf, NULL, threadProcPerformance, NULL);
    usleep(50000);
    // 端口    
    pthread_t tPort;
    pthread_create(&tPort, NULL, threadPort, NULL);
    usleep(50000);
    //定时上报agent状态
    pthread_t tHeartbeat;
    pthread_create(&tHeartbeat, NULL, threadHeartBeat, NULL);
    usleep(50000);

    // 启动线程
    pthread_t tLoading;
    pthread_create(&tLoading, NULL, threadLoading, NULL);    
    g_businessMap.init(g_confMap);
    for(std::map<std::string, LogPair>::iterator it = g_confMap.begin(); it != g_confMap.end(); ++it) {
        // 从内存中读取配置
        std::string path       = it->first;
        const LogPair& logPair = it->second;
        std::string style      = logPair._style;
        std::string format     = logPair._format;
        // 创建并启动线程
        threadArg arg(path, style, format);
        pthread_t tLog;
        pthread_create(&tLog, NULL, threadLog, (void*)&arg);
        g_confThread[path] = tLog;        
        std::cout << "input map , key = " << path << std::endl;
        usleep(50000);
    }        
    usleep(50000);
    pthread_join(tLoading, NULL);

    // 收尾工作
    if(g_rkt != NULL) {
        rd_kafka_topic_destroy(g_rkt);        
    }
    if(g_rk != NULL) {
        rd_kafka_flush(g_rk, 1*1000 /* wait for max 10 seconds */);
        rd_kafka_destroy(g_rk);        
    }
    return 0;
}
