define([], function () {
    'use strict';

    /**
     * 本地存储类
     * eg--------------------------------------------
     * js: var dao = new DAO('prefix', '--');
     * --------------------------------------------eg
     * 说明：由于prefix不同，使得我们使用同样的键值，set('key', 'value'),get('key')，却不容易冲突覆盖
     * @param       preId           本地存储数据库前缀
     * @param       timeSign        时间戳与存储数据之间的拼接符     
     */

    function DAO(preId, timeSign) {
        if (this instanceof DAO) {
            this.preId = preId || '$_$';
            this.timeSign = timeSign || '|-|';
        } else {
            return new DAO(preId, timeSign);
        }
    }

    DAO.prototype = {
        // 操作状态
        status: {
            SUCCESS: 0,     // 成功
            FAILUER: 1,     // 失败
            OVERFLOW: 2,    // 溢出
            TIMEOUT: 3      // 过期
        },
        // 保存本地存储链接
        storage: sessionStorage || window.sessionStorage,
        // 获取本地存储数据库真实字段
        getKey: function (key) {
            return this.preId + key;
        },
        /**
         * 添加（修改）数据
         * @param       key         数据字段标识
         * @param       value       数据值
         * @param       callback    回调函数
         * @param       time        添加时间
         **/
        set: function (key, value, callback, time) {
            // 设置默认操作状态
            var status = this.status.SUCCESS,
                // 获取真实字段
                key = this.getKey(key);
            try {
                // 传入时间参数时，获取时间戳
                time = new Date(time).getTime() || time.getTime();
            } catch (e) {
                // 未传入时间或者时间参数有误，默认时间：一个月
                time = new Date().getTime() + 1000 * 60 * 60 * 24 * 31;
            }
            try {
                // 向数据库中添加数据
                this.storage.setItem(key, time + this.timeSign + value);
            } catch (e) {
                // 添加数据失败，返回溢出状态
                status = this.status.OVERFLOW;
            }
            // 有回调函数则执行回调函数，并传入参数（操作状态，真实的数据字段标识，存储数据值）
            callback && callback.call(this, status, key, value)

        },
        /**
         * 获取数据
         * @param       key         数据字段标识
         * @param       callback    回调函数
         */
        get: function (key, callback) {
            // 默认操作状态设置为成功
            var status = this.status.SUCCESS,
                // 获取真实字段
                key = this.getKey(key),
                // 默认值为空
                value = null,
                // 拼接符的长度
                timeSignLen = this.timeSign.length,
                // 拼接符的索引号
                index,
                // 缓存当前this指向
                that = this,
                // 世界戳
                time,
                // 返回结果
                result;
            try {
                // 获取真实字段的值
                value = that.storage.getItem(key);
            } catch (e) {
                // 取值失败
                result = {
                    status: that.status.FAILUER,
                    value: null
                };
                // 若有回调函数，则执行回调函数
                callback && callback.call(this, result.status, result.value);
                // 始终返回结果
                return result;
            }
            // 取值成功，并value值不为空
            if (value) {
                // 拼接符索引号
                index = value.indexOf(that.timeSign);
                // 时间戳
                time = +value.slice(0, index);
                // 判断时间是否过期
                if (new Date(time).getTime() > new Date().getTime() || time == 0) {
                    // 该值未过期
                    value = value.slice(index + timeSignLen);
                    // 还原布尔值
                    value = value === 'true' ? true : value;
                    value = value === 'false' ? false : value;
                } else {
                    // 该值已经过了有效期
                    value = null;
                    // 更改状态为已过期
                    status = that.status.TIMEOUT;
                    // 删除该字段
                    that.remove(key);
                }
            } else {
                // 取值成功，但value值为空
                status = that.status.FAILUER;
            }

            result = {
                status: status,
                value: value
            };
            // 执行回调
            callback && callback.call(this, result.status, result.value);
            // 始终返回结果
            return result;
        },
        /**
         * 删除数据
         * @param       key         要删除的字段
         * @param       callback    回调函数
         * */
        remove: function (key, callback) {
            // 默认操作状态为失败
            var status = this.status.FAILUER,
                // 获取实际字段名称
                key = this.getKey(key),
                value = null;
            try {
                // 获取字段对应数据
                value = this.storage.getItem(key);
            } catch (e) { }
            if (value) {
                try {
                    // 删除数据
                    this.storage.removeItem(key);
                    // 设置操作状态为成功
                    status = this.status.SUCCESS;
                } catch (e) { }

            }
            // 执行回调函数
            callback && callback.call(this, status, status > 0 ? null : value.slice(value.indexOf(this.timeSign) + this.timeSign.length))
        },
        // 回调函数
        error: function (status, key, value) {
            var errorMsg = 'DAO get key failuer! status: ' + status;
            key && (errorMsg = 'DAO set key failuer! key: ' + key + '  value: ' + value + '  status: ' + status);
            status !== 0 && console.error(errorMsg)
        }
    }
    return DAO;


    // var BLS = new BaseLocalStorage();
    // BLS.set('hello', 'world', function () { console.log(arguments.toString()) })

});