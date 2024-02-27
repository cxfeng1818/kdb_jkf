<?php

namespace App\Mark\helpers;

use Dcat\Admin\Support\Helper;
use Dcat\Admin\Admin;

class Menu
{

    static public function render(){
        self::setLeftSidebarMenu();
    }

    static protected function getEngines(){
        return [
            'file' => function(){
                return self::getSidebarDataFromFile();
            },
            'database' => function(){
                return self::getSidebarDataFromDatabase();
            }
        ];
    }

    static protected function setLeftSidebarMenu(){
        $engine = config('admin.menu.engine');
        $engineMap = self::getEngines();
        // $items = $engineMap[$engine]; // 错误
        $items = $engineMap[$engine](); // 正确

        admin_inject_section(\Dcat\Admin\Admin::SECTION['LEFT_SIDEBAR_MENU'], function ()use($items) {
            $builder = Admin::menu();
            $html = '';
            foreach ($items as $item) {
                $html .= view('admin::partials.menu', ['item' => $item, 'builder' => $builder])->render();
            }

            return $html;
        });
    }

    /**
     * 框架自带左侧菜单，从数据库读取
     * @return array
     */
    static protected function getSidebarDataFromDatabase(){
        $menuModel = config('admin.database.menu_model');
        $allNodes = (new $menuModel())->allNodes();
        return Helper::buildNestedArray($allNodes);
    }

    /**
     * 左侧菜单改为从文件内容读取
     * @return array
     */
    static protected function getSidebarDataFromFile(){
        return Helper::buildNestedArray(require __DIR__ . "/menus.php");
    }

}
