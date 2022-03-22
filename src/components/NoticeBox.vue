<style>
.noticeBox {
    position: absolute;
    top: 10px;
    right: 20px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    animation: fadeInDown .3s;
    z-index: 10;
}

.noticeBoxContainer {
    position: relative;
    display: inline-block;
    width: 300px;
    padding: 20px;
    margin-bottom: 10px;
    background-color: #ffffff;
    animation: fadeInDown .3s forwards;
    text-align: center;
    box-shadow: 0px 5px 5px rgba(0,0,0,.2);
    z-index: 10;
}

.noticeBox_closeButton {
    position: absolute;
    top: 2px;
    right: 10px;
    font-size: 1rem;
    cursor: pointer;
    color: #000000ff;
}

.noticeBox_closeButton:hover {
    color: #00000055;
}

.noticebox-error {
    background-color: #ff7777;
}
.noticebox-success {
    background-color:  #77cc77;
}
</style>

<template>
    <div>
        <div>
            <div class="noticeBox" v-if="noticeboxQueue.length > 0">
                <div v-for="notice in noticeboxQueue"
                    :key="notice.message"
                    class="noticeBoxContainer" :class="{
                    'noticebox-error': notice.type == 'error',
                    'noticebox-success': notice.type == 'success'
                }">
                    <!-- <div class="noticeBox_closeButton" @click="close()">
                        <i class="fas fa-times"></i>
                    </div> -->
                    [{{notice.type}}] {{ notice.message }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import EventBus from "@/core/EventBus.js";

export default {
    name: "NoticeBox",
    data () {
        return {
            notice: '',
            noticeType: '',
            noticeboxQueue: []
        }
    },
    computed: {
    },
    created () {
        EventBus.$on('NOTICEBOX_NOTICE', (data) => {
            this.addNotice(data.notice, data.noticeType, data.time);
        });
    },
    methods: {
        addNotice(message, type = 'success', time = null) {
            if (time == null) time = 3;
            let newNotice = {message: message, type: type};
            this.noticeboxQueue.push(newNotice);
            let timeout = setTimeout(() => {
                this.noticeboxQueue.shift();
            }, time * 1000);
        }
    },
}
</script>
