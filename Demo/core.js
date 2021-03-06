/**
 * ooBase core JavaScript
 * Version RTM 2.5
 *
 * Author Jerry Shaw <jerry-shaw@live.com>
 * Author 秋水之冰 <27206617@qq.com>
 * Author 牟佳 <824762665@qq.com>
 * Author 禹赛 <783324154@qq.com>
 * Author 小丑路人 <2278757482@qq.com>
 *
 * Copyright 2015-2016 Jerry Shaw
 * Copyright 2016 秋水之冰
 * Copyright 2016 牟佳
 * Copyright 2016 禹赛
 * Copyright 2016 小丑路人
 *
 * This file is part of ooBase Core System.
 *
 * ooBase SDK is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * ooBase SDK is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ooBase Core System. If not, see <http://www.gnu.org/licenses/>.
 */

var IEAgent = /(msie) ([\w.]+)/.exec(navigator.userAgent.toLowerCase());
if (IEAgent !== null && IEAgent[2] !== undefined && IEAgent[2] < 9) window.location.href = '/unsupported.html';

var API = '/core/api.php';
var FILE = 'https://file.oobase.com/';
var HOST = 'dev.oobase.com';

document.addEventListener('DOMContentLoaded', function () {
    set_lang();
    keep_alive();
}, false);

function AJAX(object) {
    var url = object.url || API;
    var data = object.data || null;
    var access_key = object.key || null;
    var callback = object.callback || null;
    var dataType = object.dataType || 'json';
    var type = object.type ? object.type : (data ? 'POST' : 'GET');

    var HttpRequest = null;
    if (window.XMLHttpRequest) HttpRequest = new XMLHttpRequest();
    else if (window.ActiveXObject) {
        var Version = [
            'MSXML2.XMLHTTP.6.0',
            'MSXML2.XMLHTTP.5.0',
            'MSXML2.XMLHTTP.4.0',
            'MSXML2.XMLHTTP.3.0',
            'MSXML2.XMLHTTP.2.0',
            'Microsoft.XMLHTTP'
        ];

        var i, Versions = Version.length;

        for (i = 0; i < Versions; ++i) {
            try {
                HttpRequest = new ActiveXObject(Version[i]);
                break;
            } catch (e) {
                console.log(Version[i] + ' Not Support!');
            }
        }
    } else {
        console.log('AJAX Not Support!');
        return;
    }

    if (null !== HttpRequest) {
        var Query = null;
        if (null !== data) {
            if ('string' === typeof(data)) Query = data;
            else if ('object' === typeof(data)) {
                var Key, Queries = [];
                if (!Array.isArray(data)) for (Key in data) Queries.push(encodeURIComponent(Key) + '=' + encodeURIComponent(data[Key]));
                else for (Key in data) if ('string' === typeof(data[Key]['name'])) Queries.push(encodeURIComponent(data[Key]['name']) + '=' + encodeURIComponent(data[Key]['value']));
                Query = Queries.join('&');
            }
        }

        if ('GET' === type && null !== Query) {
            url += '?' + Query;
            Query = null;
        }

        HttpRequest.open(type, url, true);

        if (null !== access_key) HttpRequest.setRequestHeader('Access-Key', access_key);
        if ('POST' === type) HttpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        HttpRequest.onreadystatechange = function () {
            if (4 === HttpRequest.readyState) {
                if (200 === HttpRequest.status) {
                    if (null !== callback) callback('json' === dataType ? JSON.parse(HttpRequest.responseText) : HttpRequest.responseText);
                } else console.log('AJAX failed with HTTP Status Code: ' + HttpRequest.status);
            }
        };

        HttpRequest.send(Query);
    }
}

function getCookie(Name) {
    if (document.cookie.length > 0) {
        var start = document.cookie.indexOf(Name + '=');
        if (start != -1) {
            start = start + Name.length + 1;
            var end = document.cookie.indexOf(';', start);
            if (end == -1) end = document.cookie.length;
            return unescape(document.cookie.substring(start, end));
        } else return '';
    } else return '';
}

function setCookie(Name, Value, ExpireDays) {
    var ExpireDate = new Date();
    ExpireDate.setDate(ExpireDate.getDate() + ExpireDays);
    document.cookie = Name + '=' + escape(Value) + ';path=/' + ((ExpireDays == null) ? '' : ';expires=' + ExpireDate.toGMTString());
}

function set_lang() {
    var lang_section = document.getElementById('lang_section');
    if (null !== lang_section) {
        var sections = lang_section.querySelectorAll('li.section > a');
        [].forEach.call(sections, function (element) {
            element.addEventListener('click', function (event) {
                event.preventDefault();
                var lang = element.getAttribute('data');
                var lang_curr = getCookie('lang');
                if (lang !== lang_curr) {
                    setCookie('lang', lang, 30);
                    window.location.reload();
                }
            }, false);
        });
    }
}

function TimeToDate(Timestamp, hasTime, hasSec) {
    var Time = new Date(Timestamp * 1000);
    var Month = Time.getMonth() + 1;
    var Date = Time.getDate();
    var Hour = Time.getHours();
    var Min = Time.getMinutes();
    var Sec = Time.getSeconds();
    var DateTime = Time.getFullYear() + '-';
    DateTime += (Month < 10 ? '0' + Month : Month) + '-';
    DateTime += Date >= 10 ? Date : '0' + Date;
    if (hasTime === true) {
        DateTime += ' ' + (Hour >= 10 ? Hour : '0' + Hour) + ':';
        DateTime += Min >= 10 ? Min : '0' + Min;
        if (hasSec === true) DateTime += ':' + (Sec >= 10 ? Sec : '0' + Sec);
    }
    return DateTime;
}

function saveAs(Name, Value, Type) {
    var BlobValue;
    if (typeof window.Blob == 'function') BlobValue = new Blob([Value], {type: Type});
    else {
        var BlobBuilder = window.BlobBuilder || window.MozBlobBuilder || window.WebKitBlobBuilder || window.MSBlobBuilder;
        var Builder = new BlobBuilder();
        Builder.append(Value);
        BlobValue = Builder.getBlob(Type);
    }
    var URL = window.URL || window.webkitURL;
    var BlobURL = URL.createObjectURL(BlobValue);
    var BlobLink = document.createElement('a');
    if ('download' in BlobLink) {
        BlobLink.style.visibility = 'hidden';
        BlobLink.href = BlobURL;
        BlobLink.download = Name;
        document.body.appendChild(BlobLink);
        var Event = document.createEvent('MouseEvents');
        Event.initEvent('click', true, true);
        BlobLink.dispatchEvent(Event);
        document.body.removeChild(BlobLink);
    } else if (navigator.msSaveBlob) navigator.msSaveBlob(BlobValue, Name);
    else location.href = BlobURL;
}

function popup_top(msg) {
    var timer, notice_div, notice_id = 'notice_' + Math.random();
    var notice = document.createElement('div');
    notice.id = notice_id;
    notice.innerText = msg;
    notice.style.width = '100%';
    notice.style.padding = '10px 36px';
    notice.style.lineHeight = '16px';
    notice.style.background = '#ffc6c6';
    notice.style.borderBottom = '1px solid #ff7878';
    notice.style.textAlign = 'center';
    notice.style.top = '0';
    notice.style.left = '0';
    notice.style.zIndex = '100';
    notice.style.position = 'fixed';
    var notice_close = document.createElement('img');
    notice_close.src = '/_image/common/icon_no.png';
    notice_close.style.width = '28px';
    notice_close.style.height = '28px';
    notice_close.style.cursor = 'pointer';
    notice_close.style.top = '22px';
    notice_close.style.right = '5px';
    notice_close.style.zIndex = '101';
    notice_close.style.position = 'fixed';
    notice.appendChild(notice_close);
    document.body.appendChild(notice);
    notice_close.onclick = function () {
        clearTimeout(timer);
        notice_div = document.getElementById(notice_id);
        if (null !== notice_div) notice_div.parentNode.removeChild(notice_div);
    }
    timer = setTimeout(function () {
        notice_div = document.getElementById(notice_id);
        if (null !== notice_div) notice_div.parentNode.removeChild(notice_div);
    }, 3000);
}

function keep_alive() {
    if (HOST === location.host) {
        var timer = setInterval(function () {
            AJAX({
                'data': {'cmd': 'user/user_acc,keep_online'},
                'callback': function (data) {
                    if (data['user/user_acc/keep_online'] === false) clearInterval(timer);
                }
            });
        }, 240000);
        AJAX({
            'data': {
                'cmd': 'user/user_acc,resume_online,keep_online,key_detail,user/user_output,get_credits,get_user_info,get_user_menu',
                'map': 'user/user_output/get_credits:user_list'
            },
            'callback': function (data) {
                if (data['user/user_acc/keep_online'] === false) clearInterval(timer);
                else {
                    var user_home = document.getElementById('user_home');
                    if (null !== user_home) user_home.href = '/user/user.php?id=' + data['user/user_acc/key_detail']['uuid'];
                    var user_head = document.getElementById('user_head');
                    if (null !== user_head) user_head.src = data['user/user_acc/key_detail']['head'];
                    var sign_in = document.getElementById('sign_in');
                    if (null !== sign_in) {
                        sign_in.href = data['user/user_output/get_user_menu']['news']['url'];
                        sign_in.innerText = data['user/user_output/get_user_menu']['news']['text'];
                    }
                    var sign_up = document.getElementById('sign_up');
                    if (null !== sign_up) {
                        sign_up.href = data['user/user_output/get_user_menu']['panel']['url'];
                        sign_up.innerText = data['user/user_output/get_user_menu']['panel']['text'];
                    }
                }
                if (0 < data['user/user_output/get_credits'].length) {
                    var credits = document.getElementById('credits_list');
                    var Key, credits_list = '';
                    for (Key in data['user/user_output/get_user_info']) credits_list += '<span><a href="/user/user.php?id=' + Key + '" target="_blank"><img src="' + FILE + data['user/user_output/get_user_info'][Key]['user_head'] + '">' + data['user/user_output/get_user_info'][Key]['user_name'] + '</a></span>';
                    credits.innerHTML = credits_list;
                }
            }
        });
    }
}