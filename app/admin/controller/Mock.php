<?php
declare (strict_types = 1);

namespace app\admin\controller;

// 制造假数据
use think\facade\Db;

class Mock
{

    public function index()
    {
        $data = [];
        for ($i=4; $i<70; $i++) {
            $data[] = [
                'name' => 'role' . $i
            ];
        }

        Db::table('admin_role')->insertAll($data);
    }
}
