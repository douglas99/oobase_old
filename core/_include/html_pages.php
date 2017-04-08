<?php

/**
 * HTML Page Section Controlling Module
 * HTML页面部分控制模块
 *
 * Author Jerry Shaw <jerry-shaw@live.com>
 * Author 彼岸花开 <330931138@qq.com>
 * Author 秋水之冰 <27206617@qq.com>
 *
 * Copyright 2015 Jerry Shaw
 * Copyright 2015 彼岸花开
 * Copyright 2015 秋水之冰
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
class html_pages
{
    //页面部分导致的URL，具有查询值的完整URL应在此处传递
    public static $page_url = ''; //URL that the page section leads to, full URL with query values should be passed here
    //当前页码
    public static $page_curr = 1; //Current page number
    //在页面部分显示的页码，奇数包括当前页，偶数排除当前页
    public static $page_show = 5; //Page numbers that shows in the page section, odd number to include the current page, even number to exclude the current page
    //在url中传递的页面参数
    public static $page_param = 'page'; //Page parameter that passed in the url
    //在页面中显示的数据量
    public static $data_show = 10; //The quantity of data that shows in a page
    //数据量
    public static $data_total = 0; //The quantity of the data

    /**
     * Get the Page Section HTML codes
     * 获取页面部分HTML代码
     * @return string
     */
    public static function get_page_section(): string
    {
        if (0 < self::$data_total) {
            //加载语言包
            load_lib('core', 'ctrl_language');
            \ctrl_language::load('core', 'html_pages'); //Load the language pack加载语言包
            //从url获取路径和正确的查询
            //Get the path and the proper query from the url
            if ('' !== self::$page_url) {
                $url_parts = parse_url(self::$page_url);
                if (isset($url_parts['path'])) {
                    if (isset($url_parts['query'])) {
                        parse_str($url_parts['query'], $query);
                    } else {
                        $query = [];
                    }

                    $url = $url_parts['path'] . self::merge_query($query);
                    unset($query);
                } else {
                    $url = '';
                }

                unset($url_parts);
            } else {
                $url = '';
            }

            //Calculate the prev number and the next number
            //计算上一个数字和下一个数字
            $prev_num = self::$page_curr - 1;
            $next_num = self::$page_curr + 1;
            //Calculate the count of the pages
            //计算页数
            $pages = (int) ceil(self::$data_total / self::$data_show);
            //Correct the current page number
            //更正当前的页码
            if ($pages < self::$page_curr) {
                self::$page_curr = $pages;
            } elseif (1 > self::$page_curr) {
                self::$page_curr = 1;
            }

            //Calculate the page numbers that before/behind the current page
            //计算当前页面之前/之后的页码
            $page_kept = (int) (self::$page_show / 2);
            //Calculate the start and the end page number
            //计算开始和结束页码
            $page_start = self::$page_curr - $page_kept;
            $page_end   = self::$page_curr + $page_kept;
            //Process the page numbers and the href
            //处理页码和href
            $page_list = [];
            if (1 >= $page_start) {
                $stop = $pages > self::$page_show ? self::$page_show : $pages;
                for ($i = 1; $i <= $stop; ++$i) {
                    $page_list[$i] = '' !== $url ? 'href="' . $url . $i . '"' : '';
                }

                unset($stop);
            } elseif ($pages > $page_end) {
                for ($i = $page_start; $i <= $page_end; ++$i) {
                    $page_list[$i] = '' !== $url ? 'href="' . $url . $i . '"' : '';
                }
            } else {
                $start = $pages - self::$page_show + 1;
                for ($i = $start; $i <= $pages; ++$i) {
                    $page_list[$i] = '' !== $url ? 'href="' . $url . $i . '"' : '';
                }

                unset($start);
            }
            //Get the ClassName for the elements in the beginning part
            //获取开始部分中元素的ClassName
            if (1 < self::$page_curr) {
                $show_prev = 'page_btn_show';
                if (1 < $page_start && $pages > self::$page_show) {
                    $show_begin = $show_scroll_prev = 'page_btn_show';
                } else {
                    $show_begin = $show_scroll_prev = 'page_btn_hide';
                }

            } else {
                $show_prev = $show_begin = $show_scroll_prev = 'page_btn_hide';
            }

            //Get the ClassName for the elements in the ending part
            //获取结尾部分中元素的ClassName
            if ($pages > self::$page_curr) {
                $show_next = 'page_btn_show';
                if ($pages > $page_end && $pages > self::$page_show) {
                    $show_end = $show_scroll_end = 'page_btn_show';
                } else {
                    $show_end = $show_scroll_end = 'page_btn_hide';
                }

            } else {
                $show_next = $show_end = $show_scroll_end = 'page_btn_hide';
            }

            //Process the href codes for the beginning part and the ending part
            //处理起始部分和结束部分的href代码
            if ('' !== $url) {
                $prev_href  = 'href="' . $url . $prev_num . '"';
                $begin_href = 'href="' . $url . 1 . '"';
                $next_href  = 'href="' . $url . $next_num . '"';
                $end_href   = 'href="' . $url . $pages . '"';
            } else {
                $prev_href = $begin_href = $next_href = $end_href = '';
            }

            //Beginning part
            //开始部分
            $html_codes = ' <div class="pages"> <span class="page_btn"> ';
            //Generating HTML codes in the Beginning part
            //在开始部分生成HTML代码
            $html_codes .= ' <a page-data="' . $prev_num . '" ' . $prev_href . ' class="' . $show_prev . '">' . gettext('page_prev') . '</a> ';
            $html_codes .= ' <a page-data="' . 1 . '" ' . $begin_href . ' class="' . $show_begin . ' min_page">1</a> ';
            $html_codes .= ' <a class="' . $show_scroll_prev . ' scroll_prev">...</a> ';
            //Generating HTML codes for the page section
            //为页面部分生成HTML代码
            $html_codes .= ' </span> <span class="page_section" page-data="' . $url . '"> ';
            foreach ($page_list as $num => $href) {
                $page_class = self::$page_curr !== $num ? 'normal' : 'current';
                $html_codes .= ' <a page-data="' . $num . '" ' . $href . ' class="' . $page_class . '">' . $num . '</a> ';
            }
            //Ending part
            //结束部分
            $html_codes .= ' </span> <span class="page_btn"> ';
            //Generating HTML codes in the Ending part
            //在结束部分生成HTML代码
            $html_codes .= ' <a class="' . $show_scroll_end . ' scroll_next">...</a> ';
            $html_codes .= ' <a page-data="' . $pages . '" ' . $end_href . ' class="' . $show_end . ' max_page">' . $pages . '</a> ';
            $html_codes .= ' <a page-data="' . $next_num . '" ' . $next_href . ' class="' . $show_next . '">' . gettext('page_next') . '</a> ';
            $html_codes .= ' </span> </div> ';
            unset($url, $prev_num, $next_num, $pages, $page_kept, $page_start, $page_end, $page_list, $i, $show_prev, $show_begin, $show_scroll_prev, $show_next, $show_end, $show_scroll_end, $prev_href, $begin_href, $next_href, $end_href, $num, $href, $page_class);
        } else {
            $html_codes = '';
        }

        return $html_codes;
    }

    /**
     * Get the URL QUERY that with page param but without the page value
     * 使用页面参数获取URL QUERY，但没有页面值
     *
     * @param array $query
     *
     * @return string
     */
    private static function merge_query(array $query): string
    {
        $query_params   = [];
        $query_brackets = rawurlencode('[]');
        if (!empty($query) && isset($query[self::$page_param])) {
            unset($query[self::$page_param]);
        }
//Unset the param key from the query array
        //从查询数组中取消设置param键
        foreach ($query as $key => $values) {
            if (!is_array($values)) {
                $query_params[] = $key . '=' . $values;
            } else {
                foreach ($values as $value) {
                    $query_params[] = $key . $query_brackets . '=' . $value;
                }
            }

        }
        //将页面参数添加到查询参数的末尾，并将值留空以供进一步使用
        $query_params[] = self::$page_param . '='; //Add the page param onto the end of query params, and leave the value empty for further usage
        //使用页面参数获取URL QUERY，但没有页面值
        $query_value = '?' . implode('&', $query_params); //Get the URL QUERY that with page param but without the page value
        unset($query, $query_params, $query_brackets, $key, $values, $value);
        return $query_value;
    }
}
