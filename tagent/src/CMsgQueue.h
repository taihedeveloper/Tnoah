#pragma once
#include <cstdio>
#include <queue>
#include <iostream>
#include <string>
#include "ILock.h"

/* 多线程消息队列(单例) 
*  用于Agent，多个生产者，单个消费者，因此 push加锁而pop不需要加锁
*/
class CMsgQueue {
public:
    void push(const std::string& str) {
        CMyLock lock = CMyLock(_mutex);  // 加锁
        _queue.push(str);
    }

    std::string pop() {
        if(!_queue.empty()) { 
            std::string ret = _queue.front();
            _queue.pop();
            return ret;
        }else {
            return "";
        }
    }

    static CMsgQueue* getInstance() {
        if(_instance == NULL) {
            _instance = new CMsgQueue();
        }
        return _instance;
    }

    int getSz() {
        return _queue.size();
    }

    ~CMsgQueue() {}
private:
    std::queue<std::string> _queue;
    CMutex _mutex;
    static CMsgQueue* _instance;
private:
    CMsgQueue(){}
    CMsgQueue(const CMsgQueue*) {}
    CMsgQueue& operator=(const CMsgQueue&);
};

CMsgQueue* CMsgQueue::_instance = NULL;
