<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\stock\model\stock;

use app\model\BaseModel;
use think\facade\Db;

/**
 * 导入
 * @author Administrator
 */
class Import extends BaseModel
{

    /**
     * 单据导入
     * @param $path
     * @param $type
     */
    public function import($path, $type)
    {

        $PHPReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');

        //载入文件
        $PHPExcel = $PHPReader->load($path);

        //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
        $currentSheet = $PHPExcel->getSheet(0);

        //获取总行数
        $allRow = $currentSheet->getHighestRow();

        $time = $PHPExcel->getActiveSheet()->getCell('A2')->getValue();
        $time = trim(str_replace('入库时间：', '', $time));
        $time = $time ? date_to_time($time) : time();

        $document_no = $PHPExcel->getActiveSheet()->getCell('C2')->getValue();
        $document_no = trim(str_replace('编号：', '', $document_no));

        $remark = $PHPExcel->getActiveSheet()->getCell('A3')->getValue();
        $remark = trim(str_replace('备注：', '', $remark));

        dump($time, $document_no, $remark);

        dd($allRow);


//        switch ($type){
//            case 'PURCHASE'://采购入库单
//
//
//                break;
//        }


    }

}
