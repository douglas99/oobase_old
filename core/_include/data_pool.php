<?php

/**
 * Data Controlling Module
 * 数据控制模块
 *
 * Author Jerry Shaw <jerry-shaw@live.com>
 * Author 秋水之冰 <27206617@qq.com>
 * Author 杨晶 <752050750@qq.com>
 * Author 风雨凌芸 <tianpapawo@live.com>
 *
 * Copyright 2015-2017 Jerry Shaw
 * Copyright 2016-2017 秋水之冰
 * Copyright 2017 杨晶
 * Copyright 2016 风雨凌芸
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
class data_pool
{
    //CLI data
    //CLI数据
    public static $cli = [];

    //Data package
    //数据包
    public static $data = [];

    //Result data pool
    //结果数据池
    public static $pool = [];

    //Result data format (json/raw)
    //结果数据格式（json / raw）
    public static $format = 'json';

    //Module list
    //模块列表
    private static $module = [];

    //Method list
    //方法列表
    private static $method = [];

    //Keymap list
    //键值对列表
    private static $keymap = [];

    //Data Structure
    //数据结构
    private static $struct = [];

    /**
     * Initial Data Controlling Module
     * 初始数据控制模块
     * Only static methods are supported
     * 只支持静态方法
     */
    public static function start()
    {
        //Get date from HTTP Request or CLI variables
        //从HTTP请求或CLI变量获取日期
        $data = 'cli' !== PHP_SAPI ? (!ENABLE_GET ? $_POST : $_REQUEST) : self::$cli;
        //Set result data format according to the request
        //根据请求设置结果数据格式
        if (isset($data['format']) && in_array($data['format'], ['json', 'raw'], true)) {
            self::$format = &$data['format'];
        }

        //Parse "cmd" data from HTTP Request
        //从HTTP请求解析“cmd”数据 cmd=fruit_picker/picker&color=red&shape=round
        if (isset($data['cmd']) && is_string($data['cmd']) && false !== strpos($data['cmd'], '/')) {
            self::parse_cmd($data['cmd']);
        }

        //Parse "map" data from HTTP Request
        //从HTTP请求解析“map”数据
        if (isset($data['map']) && is_string($data['map']) && false !== strpos($data['map'], '/') && false !== strpos($data['map'], ':')) {
            self::parse_map($data['map']);
        }

        //Unset "format" & "cmd" & "map" from request data package
        //从请求数据包中取消设置“format”＆“cmd”＆“map”
        unset($data['format'], $data['cmd'], $data['map']);
        //Store data package to data pool
        //将数据包存储到数据池
        self::$data = &$data;//cmd =>fruit_picker/picker.color=>red,shape=round
        //Merge "$_FILES" into data pool if exists
        //如果存在，将“$ _FILES”合并到数据池中
        if (!empty($_FILES)) {
            self::$data = array_merge(self::$data, $_FILES);
        }

        //Continue running if requested data is ready
        //如果请求的数据准备就行，继续运行
        if (!empty(self::$module) && (!empty(self::$method) || !empty(self::$data))) {
            //Build data structure
            //构建数据结构
            self::$struct = array_keys(self::$data);//cmd,color,shape
            //Parse Module & Method list
            //解析模块和方法列表 $module['fruit_picker']['picker']
            foreach (self::$module as $module => $libraries) {
                //Load Module CFG file for the first time
                //首次加载模块CFG文件
                load_lib($module, 'cfg');
                //Load Libraries
                //加载库
                foreach ($libraries as $library) {
                    //Load library file
                    //加载库文件
                    $class = load_lib($module, $library);
                    //Check the load status of the class
                    //检查class类的负载状态
                    if ('' !== $class) {
                        //Get method list from the class
                        //从类中获取方法列表
                        $method_list = get_class_methods($class);
                        //Security Checking
                        //安全检查
                        if (SECURE_API) {
                            //Checking API Safe Zone
                            //检查API安全区
                            $api_list = isset($class::$api) && is_array($class::$api) ? array_keys($class::$api) : [];
                            //$api_list = array(size','shape','taste','smell','guess','color')
                            //Get api methods according to requested methods or all methods will be stored in the intersect list if no method is provided
                            //根据请求的方法获取api方法，如果没有提供方法，所有方法将被存储在交叉列表中
                            $method_api = !empty(self::$method) ? array_intersect(self::$method, $api_list, $method_list) : array_intersect($api_list, $method_list);
                            //$method_api = array('size','shape','taste','smell','guess','color')
                            //Calling "init" method at the first place if exists without API permission and data structure comparison
                            //如果存在没有API权限和数据结构比较，则调用“init”方法
                            if (in_array('init', $method_list, true) && !in_array('init', $method_api, true)) {
                                self::call_method($module, $class, 'init');
                                // self::call_method('fruit_picker', 'pick', 'init');
                            }

                            //Go through every method in the api list with API Safe Zone checking
                            //通过API安全区域检查，了解api列表中的每个方法
                            //$method_api = array('size','shape','taste','smell','guess','color')
                            foreach ($method_api as $method) {
                                //Get the intersect list of the data requirement structure
                                //获取数据需求结构的相交列表
                                $intersect = array_intersect(self::$struct, $class::$api[$method]);

                                //Get the different list of the data requirement structure
                                //获取数据需求结构的不同列表
                                $difference = array_diff($class::$api[$method], $intersect);
                                //Calling the api method if the data structure is matched
                                //如果数据结构匹配，则调用api方法
                                if (empty($difference)) {
                                    self::call_method($module, $class, $method);
                                }

                            }
                        } elseif (!empty(self::$method)) {
                            //Requested methods is needed when API Safe Zone checking is turned off
                            //当API安全区检查关闭时，需要方法
                            $method_api = array_intersect(self::$method, $method_list);
                            //Calling "init" method at the first place if exists without API permission and data structure comparison
                            //如果存在没有API权限和数据结构比较，则调用“init”方法
                            if (in_array('init', $method_list, true) && !in_array('init', $method_api, true)) {
                                self::call_method($module, $class, 'init');
                            }

                            //Calling the api method without API Safe Zone checking
                            //调用api方法没有API安全区检查
                            foreach ($method_api as $method) {
                                self::call_method($module, $class, $method);
                            }

                        }
                    } else {
                        continue;
                    }

                }
            }
            unset($module, $libraries, $library, $class, $api_list, $method_list, $method_api, $method, $intersect, $difference);
        }
        unset($data);
    }

    /**
     * "cmd" value parser “cmd”值解析器
     *
     * @param string $data
     *
     * "cmd" value should at least contain "/", or with "," for specific methods calling
     * Format should be some string like but no need to be exact as, examples as follows:
     * One module calling: "module_1/library_1"
     * One module calling with one or more methods: "module_1/library_1,method_1" or "module_1/library_1,method_1,method_2,..."
     * Multiple modules calling: "module_1/library_1,module_2/library_2,..."
     * Multiple modules calling with methods: "module_1/library_1,module_2/library_2,...,method_1,method_2,method_3,method_4,..."
     * Modules with namespace: "module_1/\namespace\library_1" or "module_1/\namespace\library_1,method_1,method_2,..."
     * Mixed modules: "module_1/\namespace\library_1,module_2/library_2"
     * Mixed modules with methods: "module_1/\namespace\library_1,module_2/library_2,...,method_1,method_2,method_3,method_4,..."
     * All mixed: "module_1/\namespace\library_1,method_1,method_2,module_2/library_2,method_3,method_4,..."
     * Notice: The key to calling a method in a module is the structure of data. All/Specific methods will only run with the matched data structure.
     * “cmd”值应至少包含“/”，或对于特定的方法调用使用“,”
     * 格式应该是一些字符串，但不需要如下所示：
     * 一个模块调用：“module_1/library_1”
     * 一个模块通过一个或多个方法调用：“module_1/library_1,method_1”或“module_1/ library_1,method_1,method_2,...”
     * 多个模块调用：“module_1/library_1,module_2/library_2,...”
     * 使用方法调用多个模块：“module_1/library_1,module_2/library_2,...,method_1,method_2,method_3，method_4，...”
     * 具有命名空间的模块：“module_1/\namespace\library_1”或“module_1/\namespace\library_1,method_1,method_2,...”
     * 混合模块： "module_1/\namespace\library_1,module_2/library_2"
     * 混合模块与方法： "module_1/\namespace\library_1,module_2/library_2,...,method_1,method_2,method_3,method_4,..."
     * 全混合: "module_1/\namespace\library_1,method_1,method_2,module_2/library_2,method_3,method_4,..."
     * 注意：在模块中调用方法的关键是数据的结构。所有/具体方法只能使用匹配的数据结构运行。
     */
    private static function parse_cmd(string $data)
    {
        //Extract "cmd" values
        //提取cmd的值 fruit_picker/Fpicker
        if (false !== strpos($data, ',')) {
            //Spilt "cmd" value if multiple modules/methods exist with ","
            //如果多个模块/方法存在“，”溢出“cmd”值
            $cmd = explode(',', $data);
            $cmd = array_filter($cmd);
            $cmd = array_unique($cmd);
        } else {
            $cmd = [$data];
        }

        //[user/user_acc,resume_online,keep_online,key_detail,user/user_output,get_credits,get_user_info,get_user_menu][user/user_output/get_credits:user_list]
        //
        //Parse "cmd" values
        //解析“cmd”值 $cmd = ['fruit_picker/picker']
        foreach ($cmd as $item) {
            //Get the position of module path
            //获取模块路径的位置
            $position = strpos($item, '/');
            if (false !== $position) {
                //Module goes here
                //Get module and library names
                //模块到这里
                //获取模块和库名称
                $module  = substr($item, 0, $position);
                $library = substr($item, $position + 1);
                //Make sure the parsed results are available
                //确保已解析的结果可用
                if (false !== $module && false !== $library) {
                    //Add module to "self::$module" if not added
                    //如果没有添加，将模块添加到“self::$module”
                    if (!isset(self::$module[$module])) {
                        self::$module[$module] = [];
                    }

                    //Add library to "self::$module" if not added
                    //如果没有添加，将添加库到 "self::$module"
                    if (!in_array($library, self::$module[$module], true)) {
                        self::$module[$module][] = $library;
                    }
                    //$module['fruit_picker']['picker']
                } else {
                    continue;
                }

            } else {
                //Method goes here
                //Add to "self::$method" if not added
                /*方法到这里
                  如果没有添加，添加到“self::$method”*/
                if (!in_array($item, self::$method, true)) {
                    self::$method[] = $item;
                }

            }
        }
        unset($data, $cmd, $item, $position, $module, $library);
    }

    /**
     * "map" value parser
     * “map”值解析器
     *
     * @param string $data
     *
     * "map" value should at least contain "/" and ":", or with "," for multiple result mapping
     * Format should be some string like but no need to be exact as, examples as follows:
     * Full result mapping: "module_1/library_1/method_1:key_1,..."
     * Deep structure mapping: "module_1/library_1/method_1/key_A/key_B/key_C:key_1,..."
     * Mixed mapping: "module_1/library_1/method_1:key_1,module_1/library_1/method_1/key_A/key_B/key_C:key_1,..."
     * Module with namespace: "module_1/\namespace\library_1/method_1:key_1,module_2/\namespace\library_2/method_2/result_key:key_2,..."
     * Notice: API running follows the input sequence, the former content will be replaced if the coming one has the same key.
     * “map”值应至少包含“/”和“：”，或对于多个结果映射使用“，”
     * 格式应该是一些字符串，但不需要如下所示：
     * 全结果映射： "module_1/library_1/method_1:key_1,..."
     * 深层结构映射： "module_1/library_1/method_1/key_A/key_B/key_C:key_1,..."
     * 混合映射： "module_1/library_1/method_1:key_1,module_1/library_1/method_1/key_A/key_B/key_C:key_1,..."
     * 具有命名空间的模块 "module_1/\namespace\library_1/method_1:key_1,module_2/\namespace\library_2/method_2/result_key:key_2,..."
     * 注意：API运行遵循输入序列，如果下一个内容具有相同的键，前一个内容将被替换。
     */
    private static function parse_map(string $data)
    {
        //Extract "map" values
        //提取 "map" 的值
        if (false !== strpos($data, ',')) {
            //Spilt "map" value if multiple modules/methods exist with ","
            //如果存在多个模块/方法，则出现溢出的“map”值“，”
            $map = explode(',', $data);
            $map = array_filter($map);
            $map = array_unique($map);
        } else {
            $map = [$data];
        }

        //Deeply parse the map values
        //深入分析map值
        foreach ($map as $value) {
            //Every map value should contain both "/" and ":"
            //每个map值都应包含“/”和“：”
            $position = strpos($value, ':');
            if (false !== strpos($value, '/') && false !== $position) {
                //Extract and get map "from" and "to"
                //提取map的‘form’与‘to’值
                $map_from = substr($value, 0, $position);
                $map_to   = substr($value, $position + 1);
                //Deeply parse map "from"
                //深入剖析map值得“from”
                if (false !== strpos($map_from, '/')) {
                    $keys = explode('/', $map_from);
                    //Map keys should always be greater than 3
                    //map键应始终大于3
                    if (3 <= count($keys)) {
                        $key_from = $keys[0] . '/' . $keys[1] . '/' . $keys[2];
                        unset($keys[0], $keys[1], $keys[2]);
                        $data_from = [];
                        foreach ($keys as $key) {
                            $data_from[] = $key;
                        }

                        //Save to keymap List
                        //保存到键值对列表
                        self::$keymap[$key_from] = ['from' => $data_from, 'to' => $map_to];
                    } else {
                        continue;
                    }

                } else {
                    continue;
                }

            } else {
                continue;
            }

        }
        unset($data, $map, $value, $position, $map_from, $map_to, $keys, $key_from, $data_from, $key);
    }

    /**
     * Call method and store the result for using/mapping
     * 调用方法并存储使用/映射的结果
     *
     * @param string $module
     * @param string $class
     * @param string $method
     */
    private static function call_method(string $module, string $class, string $method)
    {
        //Get a reflection object for the class method
        //获取类方法的反射对象
        $reflect = new \ReflectionMethod($class, $method);
        //Check the visibility and property of the method
        //检查方法的可见性和属性
        if ($reflect->isPublic() && $reflect->isStatic()) {
            //Try to call the method and catch the Exceptions or Errors
            //尝试调用该方法并捕获异常或错误
            try {
                //Calling method
                //调用方法
                $result = $class::$method();
                //Merge result
                //合并结果
                if (isset($result)) {
                    //Save result to the result data pool
                    //将结果保存到结果数据池
                    self::$pool[$module . '/' . $class . '/' . $method] = $result;
                    //self:$pool['fruit_picker/picker/guess'] =
                    //Check keymap request with result data
                    //检查带有结果数据的键映射请求
                    if (isset(self::$keymap[$module . '/' . $class . '/' . $method])) {
                        //Processing array result to get the final data
                        //处理数组结果得到最终数据
                        if (!empty(self::$keymap[$module . '/' . $class . '/' . $method]['from']) && is_array($result)) {
                            //Check every key in keymap from request
                            //从请求中检查keymap中的每个键
                            foreach (self::$keymap[$module . '/' . $class . '/' . $method]['from'] as $key) {
                                //Check key's existence
                                //检查key的存在
                                if (isset($result[$key])) {
                                    //Switch result data to where we find
                                    //将结果数据切换到我们找到的位置
                                    unset($tmp);
                                    $tmp = $result[$key];
                                    unset($result);
                                    $result = $tmp;
                                } else {
                                    //Unset result data if requested key does not exist
                                    //如果请求的key不存在，则取消设置结果数据
                                    unset($result);
                                    break;
                                }
                            }
                        }
                        //Map processed result data to request data pool if isset
                        //映射处理后的结果数据，如果是isset则请求数据池
                        if (isset($result)) {
                            //Caution: The data with the same key in data pool will be overwritten if exists
                            //注意：数据池中相同键的数据将被覆盖（如果存在）
                            self::$data[self::$keymap[$module . '/' . $class . '/' . $method]['to']] = $result;
                            //Rebuild data structure
                            //重建数据结构
                            self::$struct = array_keys(self::$data);
                        }
                    }
                }
            } catch ( \Exception $exception) {
                //Save the Exception or Error Message to the result data pool instead
                //将异常或错误消息保存到结果数据池
                self::$pool[$module . '/' . $class . '/' . $method] = $exception->getMessage();
            } catch(\Throwable $exception) {
                 //Save the Exception or Error Message to the result data pool instead
                self::$pool[$module . '/' . $class . '/' . $method] = $exception->getMessage();
            }
        }
    }
}
