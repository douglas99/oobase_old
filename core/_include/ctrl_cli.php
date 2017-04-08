<?php

/**
 * CLI Controlling Module CLI控制模块
 *
 * Author Jerry Shaw <jerry-shaw@live.com>
 * Author 秋水之冰 <27206617@qq.com>
 * Author Yara <314850412@qq.com>
 *
 * Copyright 2016-2017 Jerry Shaw
 * Copyright 2017 秋水之冰
 * Copyright 2017 Yara
 *
 * This file is part of NervSys.
 *
 * NervSys is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NervSys is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NervSys. If not, see <http://www.gnu.org/licenses/>.
 */
class ctrl_cli
{
    //Options
    //选项
    public static $opt = [];

    //Variables
    //变量
    public static $var = [];

    //Option Details
    //选项详细信息
    private static $opt_cmd = ''; //Option for Internal Mode 内部模式选项
    private static $opt_map = ''; //Option for Internal Mode 内部模式选项
    private static $opt_get = ''; //Result (Valid values: "cmd", "data", "error", "result" or "cmd", "map", "data", "result"; empty: no returns)
    //结果 (有效值: "cmd", "data", "error", "result" or "cmd", "map", "data", "result"; 空的: 没有返回值)
    private static $opt_log = false; //Log setting, set to true to log all ("time", "cmd", "data", "error", "result" or "time", "cmd", "map", "data", "result")
    //日志设置, 设置为true以记录所有 ("time", "cmd", "data", "error", "result" or "time", "cmd", "map", "data", "result")
    private static $opt_try  = 100; //Default try times for stream checking 流检查的默认尝试时间
    private static $opt_wait = 2; //Default time wait for stream checking (in microseconds) 默认时间等待流检查（以微秒为单位）
    private static $opt_data = ''; //Request data, will try to read STDIN when empty
    //请求数据时，会尝试在空白时读取STDIN
    private static $opt_path = ROOT . '/_cli/cfg.json'; //Default CFG file path 默认CFG文件路径

    //CLI Runtime Settings CLI运行时设置
    private static $cli_cmd = ''; //CLI Command CLI命令
    private static $cli_cfg = []; //CLI Configurations CLI 配置

    /**
     * Load options  加载选项
     */
    private static function load_opt()
    {
        if (!empty(self::$opt)) {
            //Get "log" option
            //获取“日志”选项
            if (isset(self::$opt['log']) || isset(self::$opt['l'])) {
                self::$opt_log = true;
            }

            //Get "cmd" option
            //获取“cmd”选项
            if (isset(self::$opt['cmd']) && false !== self::$opt['cmd'] && '' !== self::$opt['cmd']) {
                self::$opt_cmd = self::$opt['cmd'];
            } elseif (isset(self::$opt['c']) && false !== self::$opt['c'] && '' !== self::$opt['c']) {
                self::$opt_cmd = self::$opt['c'];
            }

            //Get "map" option
            //获取“map”选项
            if (isset(self::$opt['map']) && false !== self::$opt['map'] && '' !== self::$opt['map']) {
                self::$opt_map = self::$opt['map'];
            } elseif (isset(self::$opt['m']) && false !== self::$opt['m'] && '' !== self::$opt['m']) {
                self::$opt_map = self::$opt['m'];
            }

            //Get "get" option
            //获取“get”选项
            if (isset(self::$opt['get']) && false !== self::$opt['get'] && '' !== self::$opt['get']) {
                self::$opt_get = self::$opt['get'];
            } elseif (isset(self::$opt['g']) && false !== self::$opt['g'] && '' !== self::$opt['g']) {
                self::$opt_get = self::$opt['g'];
            }

            //Get "path" option
            //获取“path”选项
            if (isset(self::$opt['path']) && false !== self::$opt['path'] && '' !== self::$opt['path']) {
                self::$opt_path = self::$opt['path'];
            } elseif (isset(self::$opt['p']) && false !== self::$opt['p'] && '' !== self::$opt['p']) {
                self::$opt_path = self::$opt['p'];
            }

            //Get "data" from option/STDIN
            //从option / STDIN获取“data”
            if (isset(self::$opt['data']) && false !== self::$opt['data'] && '' !== self::$opt['data']) {
                self::$opt_data = self::$opt['data'];
            } elseif (isset(self::$opt['d']) && false !== self::$opt['d'] && '' !== self::$opt['d']) {
                self::$opt_data = self::$opt['d'];
            } else {
                self::$opt_data = self::get_stream([STDIN]);
            }

            //Get "try" option
            //获取“try”选项
            if (isset(self::$opt['try'])) {
                self::$opt['try'] = (int) self::$opt['try'];
                if (0 < self::$opt['try']) {
                    self::$opt_try = self::$opt['try'];
                }

            } elseif (isset(self::$opt['t'])) {
                self::$opt['t'] = (int) self::$opt['t'];
                if (0 < self::$opt['t']) {
                    self::$opt_try = self::$opt['t'];
                }

            }
            //Get "wait" option
            //获取“wait”选项
            if (isset(self::$opt['wait'])) {
                self::$opt['wait'] = (int) self::$opt['wait'];
                if (0 < self::$opt['wait']) {
                    self::$opt_wait = self::$opt['wait'];
                }

            } elseif (isset(self::$opt['w'])) {
                self::$opt['w'] = (int) self::$opt['w'];
                if (0 < self::$opt['w']) {
                    self::$opt_wait = self::$opt['w'];
                }

            }
        }
    }

    /**
     * Load configurations 加载配置
     */
    private static function load_cfg()
    {
        //Check CFG file
        //检查CFG文件
        if (is_file(self::$opt_path)) {
            //Load File Controlling Module
            //加载文件控制模块
            load_lib('core', 'ctrl_file');
            //Get CFG file content
            //获取CFG文件内容
            $json = \ctrl_file::get_content(self::$opt_path);
            if ('' !== $json) {
                //Decode file content and map to CFG
                //解码文件内容并映射到CFG
                $data = json_decode($json, true);
                if (isset($data)) {
                    self::$cli_cfg = &$data;
                }

                unset($data);
            }
            unset($json);
        }
    }

    /**
     * Build var for Internal Mode
     * 构建内部模式的变量
     */
    private static function build_var()
    {
        //Regroup request data
        //重新组合请求数据
        self::$var = ['cmd' => self::$opt_cmd];
        //Merge "map" data when exists
        //存在时合并“map”数据
        if ('' !== self::$opt_map) {
            self::$var['map'] = self::$opt_map;
        }

        //Process input data 处理输入数据
        if ('' !== self::$opt_data) {
            //Parse HTTP query data 解析HTTP查询数据
            parse_str(self::$opt_data, $data);
            //Merge input data when exists 存在时合并输入数据
            if (!empty($data)) {
                self::$var = array_merge(self::$var, $data);
            }

            unset($data);
        }
    }

    /**
     * Build CMD for External Mode
     * 构建外部模式的CMD
     */
    private static function build_cmd()
    {
        //Check variables
        //检查变量
        if (!empty(self::$var)) {
            //Check specific language in configurations
            //检查配置中的特定语言
            if (isset(self::$cli_cfg[self::$var[0]])) {
                //Rebuild all commands
                //重建所有命令
                foreach (self::$var as $k => $v) {
                    if (isset(self::$cli_cfg[$v])) {
                        self::$var[$k] = self::$cli_cfg[$v];
                    }
                }

                //Create command
                //创建命令
                self::$cli_cmd = implode(' ', self::$var);
                unset($k, $v);
            }
        }
    }

    /**
     * Save logs
     * 日志保存
     * @param array $data
     */
    private static function save_log(array $data)
    {
        $logs = array_merge(['time' => date('Y-m-d H:i:s', time())], $data);
        foreach ($logs as $key => $value) {
            $logs[$key] = strtoupper($key) . ': ' . $value;
        }

        \ctrl_file::append_content(CLI_LOG_PATH . date('Y-m-d', time()) . '.log', PHP_EOL . implode(PHP_EOL, $logs) . PHP_EOL);
        unset($data, $logs, $key, $value);
    }

    /**
     * Get the content of current stream
     * 获取当前流的内容
     *
     * @param array $stream
     *
     * @return string
     */
    private static function get_stream(array $stream): string
    {
        $try    = 0;
        $result = '';
        //Get the resource
        //获取资源
        $resource = current($stream);
        //Keep checking the stat of stream
        //继续检查流的状态
        while ($try < self::$opt_try) {
            //Get the stat of stream
            //获取流的统计信息
            $stat = fstat($resource);
            //Check the stat of stream
            //检查流的状态
            if (false !== $stat && 0 < $stat['size']) {
                //Get trimmed stream content
                //获取修剪流内容
                $result = trim(stream_get_contents($resource));
                break;
            } else {
                //Wait for process
                //等待进程
                usleep(self::$opt_wait);
                ++$try;
            }
        }
        //Return false once the elapsed time reaches the limit
        //一旦经过时间达到极限，返回false
        unset($stream, $try, $resource, $stat);
        return $result;
    }

    /**
     * Call Internal API
     * 调用内部 API
     * @return array
     */
    private static function call_api(): array
    {
        $result = [];
        //Load Data Controlling Module
        //加载数据控制模块
        load_lib('core', 'data_pool');
        //Pass data to Data Controlling Module
        //将数据传递到数据控制模块
        \data_pool::$cli = self::$var;
        //Start data_pool process
        //启动data_pool进程
        \data_pool::start();
        //Get API Result
        //获取API结果
        $data = \data_pool::$pool;
        //Save logs
        //保存日志
        if (self::$opt_log) {
            $logs           = ['cmd' => self::$opt_cmd];
            $logs['map']    = self::$opt_map;
            $logs['data']   = self::$opt_data;
            $logs['result'] = &$data;
            self::save_log($logs);
            unset($logs);
        }
        //Build results
        //构建结果
        if ('' !== self::$opt_get) {
            if (false !== strpos(self::$opt_get, 'cmd')) {
                $result['cmd'] = self::$opt_cmd;
            }

            if (false !== strpos(self::$opt_get, 'map')) {
                $result['map'] = self::$opt_map;
            }

            if (false !== strpos(self::$opt_get, 'data')) {
                $result['data'] = self::$opt_data;
            }

            if (false !== strpos(self::$opt_get, 'result')) {
                $result['result'] = &$data;
            }

        }
        unset($data);
        return $result;
    }

    /**
     * Run External Process
     * 运行外部进程
     * @return array
     */
    private static function run_exec(): array
    {
        //Check command
        //检查命令
        if ('' !== self::$cli_cmd) {
            //Run process
            //运行进程
            $process = proc_open(self::$cli_cmd, [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes, CLI_WORK_PATH);
            //Parse process data
            //解析进程数据
            if (is_resource($process)) {
                //Process input data
                //进程输入数据
                if ('' !== self::$opt_data) {
                    fwrite($pipes[0], self::$opt_data . PHP_EOL);
                }

                //Build detailed results/logs
                //构建详细的结果/日志
                $result = $logs = [];
                //Save logs
                //保存日志
                if (self::$opt_log) {
                    $logs['cmd']    = self::$cli_cmd;
                    $logs['data']   = self::$opt_data;
                    $logs['error']  = self::get_stream([$pipes[2]]);
                    $logs['result'] = self::get_stream([$pipes[1]]);
                    self::save_log($logs);
                }
                //Build results
                //构建结果
                if ('' !== self::$opt_get) {
                    if (false !== strpos(self::$opt_get, 'cmd')) {
                        $result['cmd'] = self::$cli_cmd;
                    }

                    if (false !== strpos(self::$opt_get, 'data')) {
                        $result['data'] = self::$opt_data;
                    }

                    if (false !== strpos(self::$opt_get, 'error')) {
                        $result['error'] = $logs['error'] ?? self::get_stream([$pipes[2]]);
                    }

                    if (false !== strpos(self::$opt_get, 'result')) {
                        $result['result'] = $logs['result'] ?? self::get_stream([$pipes[1]]);
                    }

                }
                //Close all pipes
                //关闭所有管道
                foreach ($pipes as $pipe) {
                    fclose($pipe);
                }

                //Close Process
                //关闭进程
                proc_close($process);
                unset($logs, $pipe);
            } else {
                $result = ['error' => 'Process ERROR!'];
            }

            unset($process, $pipes);
        } else {
            $result = ['error' => 'Command ERROR!'];
        }

        return $result;
    }

    /**
     * Start CLI
     * 启动CLI
     *
     * @return array
     */
    public static function start(): array
    {
        //Parse options
        //解析选项
        self::load_opt();
        //Detect CLI Mode
        //检测CLI模式
        if ('' !== self::$opt_cmd) {
            //Internal Mode
            //内部模式
            //Build internal var
            // 构建内部变量
            self::build_var();
            //Call API
            //调用API
            return self::call_api();
        } else {
            //External Mode
            //外部模式
            //Load CFG setting
            //加载CFG设置
            self::load_cfg();
            //Build external CMD
            //构建外部CMD
            self::build_cmd();
            //Run process
            //运行进程
            return self::run_exec();
        }
    }
}
