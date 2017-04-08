<?php

/**
 * Class picker
 *
 * 这是NervSys的示例演示模块
 * 它将显示如何以原子方式调用方法
 * 对于演示模块，所有需要的注释将被写入。
 *
 * 用法
 *
 * 1.将ENABLE_GET设为true，以便通过url GET进行更好的控制。您也可以使用POST，也不需要将其变为true。
 * 通过示例url bellow访问您的服务器：（记住更改host_address到您自己的ip /主机）
 *
 * 2. 访问 http://host_address/api.php?format=json&cmd=fruit_picker/picker&color=yellow&smell=sweet
 * 3. 访问 http://host_address/api.php?format=json&cmd=fruit_picker/picker&color=yellow&smell=sweet&shape=pear
 *
 * 4. 获得差异
 *
 * 演示URL是宽松的，这意味着所有数据结构匹配的方法将被调用
 * 如果要尝试严格的样式，请访问GET启用的示例urls：（记住将host_address更改为您自己的ip / host）
 *
 * http://host_address/api.php?format=json&cmd=fruit_picker/picker,color,smell,guess&color=yellow&smell=sweet
 * http://host_address/api.php?format=json&cmd=fruit_picker/picker,color,smell,shape,guess&color=yellow&smell=sweet&shape=pear
 *
 * 您也可以修改原始数据和请求url，无论如何。做你自己的
 * 您可以将模块链接到数据库或其他模块，以完成大型项目。
 */
//类名应与其文件名完全相同
class picker
{
    //所有的变量都应该是静态的，并照常使用它们
    public static $data_1;
    protected static $data_2;
    private static $data_3;

    //像往常一样使用const数据
    const data_4 = [];

    //以上是例子，不用
    /**
     * 我使用const声明一些具有某些属性的果实列表，
     * 结构就像数据库中刚刚出来的数据
     * 而我们需要使属性列表简单，
     * 我们假设，水果的每个财产只包含一个价值。
     * 所有的物业都应该相互交叉，使之更有意义。
     * 不要做奇怪的属性，或者，这将使简单的演示太复杂，尽管它也可以做到。
     * 这意味着，苹果可以是绿色和红色，但是，我们只采取红色作为其颜色属性
     *
     * 为了让更多的人知道这个名字，我们把水果的名字翻译成中文
     */
    const fruits = [
        [
            'name' => 'apple 苹果',
            'color' => 'red',
            'size' => 'small',
            'taste' => 'sweet',
            'shape' => 'round',
            'smell' => 'sweet'
        ],
        [
            'name' => 'pear 梨',
            'color' => 'yellow',
            'size' => 'small',
            'taste' => 'sweet',
            'shape' => 'pear',
            'smell' => 'sweet'
        ],
        [
            'name' => 'banana 香蕉',
            'color' => 'yellow',
            'size' => 'small',
            'taste' => 'sweet',
            'shape' => 'bar',
            'smell' => 'sweet'
        ],
        [
            'name' => 'watermelon 西瓜',
            'color' => 'green',
            'size' => 'big',
            'taste' => 'sweet',
            'shape' => 'round',
            'smell' => 'none'
        ],
        [
            'name' => 'mango 芒果',
            'color' => 'yellow',
            'size' => 'small',
            'taste' => 'sweet',
            'shape' => 'round',
            'smell' => 'sweet'
        ],
        [
            'name' => 'orange 桔子',
            'color' => 'yellow',
            'size' => 'small',
            'taste' => 'acidity',
            'shape' => 'round',
            'smell' => 'acidity'
        ],
        [
            'name' => 'pineapple 菠萝',
            'color' => 'yellow',
            'size' => 'medium',
            'taste' => 'acidity',
            'shape' => 'round',
            'smell' => 'acidity'
        ],
        [
            'name' => 'tomato 西红柿',
            'color' => 'red',
            'size' => 'small',
            'taste' => 'acidity',
            'shape' => 'round',
            'smell' => 'none'
        ],
        [
            'name' => 'grape 葡萄',
            'color' => 'purple',
            'size' => 'tiny',
            'taste' => 'sweet',
            'shape' => 'round',
            'smell' => 'none'
        ],
        [
            'name' => 'avocado 牛油果',
            'color' => 'green',
            'size' => 'small',
            'taste' => 'none',
            'shape' => 'round',
            'smell' => 'none'
        ]
    ];

    //Use to store the data of the correct format
    private static $data = [];
    private static $fruits = [];

    /**
     * Make an API Safe Zone for api calling
     *
     * The content format is "method name" => ["required_data_name_1", "required_data_name_2", "required_data_name_3", ...]
     * If a method need no required data, leave an empty array there like "method name" => [], or, it'll be ignored by api
     * NOTICE: Only put those required data name in the array, those data which are not required/null should not be put in the safe zone
     *
     * @var array
     */
    public static $api = [
        'color' => ['color'],
        'size' => ['size'],
        'taste' => ['taste'],
        'shape' => ['shape'],
        'smell' => ['smell'],
        'guess' => []//This method needs no data, leave an empty array here to allow it to be calling
    ];

    //Store the result for every method
    private static $result = [];

    /**
     * This is the first calling method in a class via api request without API Safe Zone checking
     * Technically, just use it doing some preparations
     * Don't use it to do important processes
     * If you don't need init function, just don't write it.
     * You module can be fully functional without init.
     * It is not required strictly.
     *
     * We use it to restructure the data to the format we need
     */
    public static function init()
    {
        //Load the data_pool module as we need it, or, just ignore it because the api also loaded it.
        load_lib('core', 'data_pool');

        //We actually know the format we need, so, do it
        //Make a copy of original data
        $raw_data = self::fruits;

        //Go over the list deeply
        foreach ($raw_data as $values) {
            foreach ($values as $key => $value) {
                //Regrouping
                if ('name' === $key) {
                    self::$fruits[] = $value;//Stored the fruit
                    $name = $value;//get the fruit's name
                } else {
                    //properties go here
                    if (!isset(self::$data[$key])) self::$data[$key][$value][] = $name;//for new property
                    else self::$data[$key][$value][] = $name;//for existed property
                }
            }
        }
        //We now should get the data with formatted structure in self::$data
    }

    /**
     * Methods bellow are processing single property, you can rewrite them shortly in one function
     * Here, we just show you that, every method is highly separated from each other.
     * NOTICE: Remember, all callable methods should NOT pass variables, all variables stored in data_pool::$data
     */

    /**
     * For color
     */
    public static function color(): array
    {
        if (isset(data_pool::$data['color']) && isset(self::$data['color'][data_pool::$data['color']])) {
            self::$result['color'] = self::$data['color'][data_pool::$data['color']];
            $result = self::$data['color'][data_pool::$data['color']];
        } else $result = self::$result['color'] = [];
        return $result;
    }

    /**
     * For size
     */
    public static function size(): array
    {
        if (isset(data_pool::$data['size']) && isset(self::$data['size'][data_pool::$data['size']])) {
            self::$result['size'] = self::$data['size'][data_pool::$data['size']];
            $result = self::$data['size'][data_pool::$data['size']];
        } else $result = self::$result['size'] = [];
        return $result;
    }

    /**
     * For taste
     */
    public static function taste(): array
    {
        if (isset(data_pool::$data['taste']) && isset(self::$data['taste'][data_pool::$data['taste']])) {
            self::$result['taste'] = self::$data['taste'][data_pool::$data['taste']];
            $result = self::$data['taste'][data_pool::$data['taste']];
        } else $result = self::$result['taste'] = [];
        return $result;
    }

    /**
     * For shape
     */
    public static function shape(): array
    {
        if (isset(data_pool::$data['shape']) && isset(self::$data['shape'][data_pool::$data['shape']])) {
            self::$result['shape'] = self::$data['shape'][data_pool::$data['shape']];
            $result = self::$data['shape'][data_pool::$data['shape']];
        } else $result = self::$result['shape'] = [];
        return $result;
    }

    /**
     * For smell
     */
    public static function smell(): array
    {
        if (isset(data_pool::$data['smell']) && isset(self::$data['smell'][data_pool::$data['smell']])) {
            self::$result['smell'] = self::$data['smell'][data_pool::$data['smell']];
            $result = self::$data['smell'][data_pool::$data['smell']];
        } else $result = self::$result['smell'] = [];
        return $result;
    }

    /**
     * Now we need to get out what you may exactly want
     */

    public static function guess()
    {
        //Make a copy of all fruits
        $wanted = self::$fruits;

        //Get the property intersected list recursively
        foreach (self::$result as $value) $wanted = array_intersect($wanted, $value);

        //You can show the guessed result data via var_dump here
        //var_dump($wanted);

        //Return the result
        return $wanted;
    }
    public static function picker_data()
    {
        return self::$data;
    }
    public static function picker_name()
    {
        return self::$fruits;
    }
}
