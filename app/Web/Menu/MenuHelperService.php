<?php

namespace App\Web\Menu;

use Illuminate\Support\Facades\DB;

class MenuHelperService
{
    public function getParentBackendMenu(): array
    {
        $result = DB::table('cms_menus')
            ->select('id', 'title_en', 'title_mr', 'parent_id')
            ->where('is_active', config('constant.ACTIVE'))
            ->whereNotIn('title_en', ['Blog', 'Feedback', 'Wishlist'])
            ->get()
            ->toArray();

        return json_decode(json_encode($result), true);
    }

    public function buildTree(array $data, $parentId = 0): array
    {
        $tree = [];

        foreach ($data as $item) {
            if ($item['parent_id'] == $parentId) {
                $children = $this->buildTree($data, $item['id']);
                if (!empty($children)) {
                    $item['_children'] = $children;
                }
                $tree[] = $item;
            }
        }

        return $tree;
    }

    public function getSubMenuOptions($selected = null): array|string
    {
        $list = [
            1 => __('message.yes'),
            2 => __('message.no'),
        ];

        return $selected ? $list[$selected] : $list;
    }

    public function getTemplateOptions($selected = null): array|string
    {
        $list = [
            2 => __('message.dynamic_page'),
        ];

        return $selected ? $list[$selected] : $list;
    }

    public function getMenuSortOrder($order = null): array
    {
        $menuOrders = $this->getExistingMenuOrder();
        $used = array_column($menuOrders, 'sort_order');
        $range = range(1, 50);

        foreach ($used as $val) {
            if (($key = array_search($val, $range)) !== false) {
                unset($range[$key]);
            }
        }

        if ($order) {
            $range[] = $order;
        }

        sort($range);
        return array_combine($range, $range);
    }

    public function getExistingMenuOrder($menuId = null): array
    {
        $query = DB::table('cms_menus')->select('sort_order');

        if (!empty($menuId)) {
            $query->where('parent_id', $menuId);
        } else {
            $query->whereNull('parent_id');
        }

        $query->where('is_active', config('constant.ACTIVE'));

        return json_decode(json_encode($query->get()), true);
    }

    public function getFMenuCategories(): array
    {
        return [
            '' => 'Select',
            'Links' => 'Links',
            'Quick Links' => 'Quick Links',
            'Resources' => 'Resources',
            'Usefull Links' => 'Usefull Links',
        ];
    }

    public function getFMenuCategoryHindi($key = null): array|string
    {
        $list = [
            '' => 'Select',
            'Links' => 'लिंक',
            'Quick Links' => 'त्वरित सम्पक',
            'Resources' => 'संसाधन',
            'Usefull Links' => 'उपयोगी कड़ियाँ',
        ];

        return $key ? $list[$key] : $list;
    }

    static public function generateHindiSlug(string $string): string
    {
        $string = trim(strtolower($string));
        $string = preg_replace("/[^a-z0-9_ोौेैा्ीिीूुंःअआइईउऊएऐओऔकखगघचछजझञटठडढतथदधनपफबभमयरलवसशषहश्रक्षटठडढङणनऋड़\s-]/u", "", $string);
        $string = preg_replace("/[\s-]+/", " ", $string);
        $string = preg_replace("/[\s]/", '-', $string);

        return $string;
    }
}
