<?php
namespace app\index\controller;

use app\index\model\Notices;
use app\index\model\Seo;
use app\index\model\Server;
use think\Controller;
use app\index\model\Main;
use think\Db;
use think\File;

class Index extends Controller
{
    private $pageSize = 30;

    public function index()
    {

        if (isset($_POST['file_data_import']) && intval($_POST['file_data_import']) === 1) {

            if (isset($_FILES['file_datas']['type']) && !empty($_FILES['file_datas']['type'])) {
                $type = $_FILES['file_datas']['type'];

                switch ($type){
                    case 'application/vnd.ms-excel':
                        $res = $this -> excelUploadInsert();
                        break;
                    case 'text/plain':
                        $res = $this -> txtUploadInsert();
                        break;
                    default:
                        return false;
                }

            }
            if (isset($res)) {
                echo '<script>alert("共处理'.$res['allNum'].'条数据，'.($res["allNum"]-$res['flgNum']).'条数据处理失败或无需处理")</script>';
            }

        }

        return $this -> fetch();
    }

    //网站列表
    public function webList($pageNow = 1, $order = [], $pageSize = 0)
    {
        $web = new Main();
        if (!is_array($order)){
            $order = json_decode($order, true);
        }

        $pageNow = intval($pageNow) > 0 ? intval($pageNow) : 1;
        $pageSize = intval($pageSize) > 0 ? intval($pageSize) : $this -> pageSize;

        $res = $web -> getList($pageNow, $order, $pageSize);

        return json_encode($res);
    }

    //增加网站信息
    public function addWebInfo()
    {
        $res = null;

        if (isset($_POST['webInfo']) && !empty($_POST['webInfo'])){

            $sv = isset($_POST['sv']) ? intval($_POST['sv']) : 0;
            $s = isset($_POST['s']) ? intval($_POST['s']) : 0;

            //是否有附表数据一起添加
            if ($sv || $s) {
                //开启事务
               $domainID = Db::transaction(function (){
                   $sv = isset($_POST['sv']) ? intval($_POST['sv']) : 0;
                   $s = isset($_POST['s']) ? intval($_POST['s']) : 0;
                    $data = $_POST['webInfo'];
                    $data = json_decode($data,true);
                    $mainData = [];
                    $web = new Main();

                    //检验数据domain是否存在
                   $checkID = Main::where('domain',trim($data['domain']))->value('domain_id');
                   if ($checkID) return false;

                    $mainCol = $web -> getColumn();
                    foreach ($mainCol as $key=>$value ){
                        if ($key=='domain_id')
                            continue;

                        $mainData[$key] = $data[$key];
                    }
                    $domainID = $web -> webInsert($mainData);
                    $data['domain_id'] = $domainID;


                    if ($sv) {
                        $serverData = [];
                        $server = new Server();
                        $serverCol = $server -> getColumn();
                        foreach ($serverCol as $key=>$value ){
                            if ($key=='server_id')
                                continue;

                            $serverData[$key] = $data[$key];
                        }
                        $server -> db() ->insert($serverData);
                    }


                   if ($s) {
                       $seoData = [];
                       $seo = new Seo();
                       $seoCol = $seo -> getColumn();
                       foreach ($seoCol as $key=>$value ){
                           if ($key=='seo_id')
                               continue;

                           $seoData[$key] = $data[$key];
                       }
                       $seo -> db() ->insert($seoData);
                   }

                   return $domainID;
                });

            } else {
                //只有main表数据
                $web = new Main();
                $data = $_POST['webInfo'];
                $data = json_decode($data,true);
                $mainData = [];
                $mianCol = $web -> getColumn();
                foreach ($mianCol as $key=>$value ){
                    if ($key=='domain_id')
                        continue;

                    $mainData[$key] = $data[$key];
                }
                $domainID = $web -> webInsert($mainData);
            }

            var_dump($domainID);
            exit();
            return $domainID;

        }

       return null;
    }


    public function showMSVInfo($pageNow = 1, $order = [], $pageSize = 0)
    {
        if (!is_array($order)){
            $order = json_decode($order, true);
        }
        $server = new Server();
        $pageNow = intval($pageNow) > 0 ? intval($pageNow) : 1;
        $pageSize = intval($pageSize) > 0 ? intval($pageSize) : $this -> pageSize;
        $res = $server -> getMSVInfo($pageNow, $order, $pageSize);

        return json_encode($res);
    }

    public function showMSInfo($pageNow = 1, $order = [], $pageSize = 0)
    {
        if (!is_array($order)){
            $order = json_decode($order, true);
        }
        $seo = new Seo();
        $pageNow = intval($pageNow) > 0 ? intval($pageNow) : 1;
        $pageSize = intval($pageSize) > 0 ? intval($pageSize) : $this -> pageSize;
        $res = $seo -> getMSInfo($pageNow, $order, $pageSize);

        return json_encode($res);
    }

    public function showMSVSInfo($pageNow = 1, $order = [], $pageSize = 0)
    {
        if (!is_array($order)){
            $order = json_decode($order, true);
        }
        $main = new Main();
        $pageNow = intval($pageNow) > 0 ? intval($pageNow) : 1;
        $pageSize = intval($pageSize) > 0 ? intval($pageSize) : $this -> pageSize;
        $res = $main -> getMSVSInfo($pageNow, $order, $pageSize);

        return json_encode($res);
    }

    //获取表头信息
    public function getCol()
    {
        $sv = $_POST['sv'];
        $s = $_POST['s'];
        if ($sv || $s) {
            $table = 'main';
            if ($sv) {
                $table = $table.'_server';
            }
            if ($s) {
                $table = $table.'_seo';
            }
        } else {
            $table = '';
        }
        $main = new Main();
        $res = $main -> getColumn($table);

        return json_encode($res);
    }

    //网站列表数据删除
    public function webDel()
    {


        if (isset($_POST['domain_id']) || !empty($_POST['domain_id'])) {

           $res = Db::transaction(function (){
                $domainID = intval(trim($_POST['domain_id']));

                $seo = new Seo();
                $seo -> del($domainID, 'seo_id');

                $server = new Server();
                $server -> del($domainID, 'server_id');

                $notice = new Notices();
                $notice -> del($domainID, 'id');

                $main = new Main();

                return $main -> del($domainID,'domain_id');

            });

           return $res;
        }

            return false;

    }

    //网站列表数据更新
    public function update()
    {

        if (isset($_POST['field']) && !empty($_POST['field']) && isset($_POST['domainID']) && !empty($_POST['domainID'])){
            $data = json_decode($_POST['field'],true);
            $tab = trim($_POST['table']);

            if (isset($data['web_lrat'])) {
                $data['web_lrat'] = strtotime($data['web_lrat']);
            }
            if (isset($data['web_srat'])) {
                $data['web_srat'] = strtotime($data['web_srat']);
            }
            if (isset($data['domain_rat'])) {
                $data['domain_rat'] = strtotime($data['domain_rat']);
            }
            if (isset($data['agent_rat'])) {
                $data['agent_rat'] = strtotime($data['agent_rat']);
            }

            switch ($tab){
                case 'm':
                    $table = 'web_main';
                    break;
                case 'sv':
                    $table = 'web_server';

                    if (isset($data['ftp_ip']) && empty($data['ftp_ip'])){
                        return false;
                    }
                    if (isset($data['mysql_ip']) && empty($data['mysql_ip'])){
                        return false;
                    }

                    break;
                case 's':
                    $table = 'web_seo';
                    if (@!$data['key_words']){
                        return false;
                    }
                    break;
                default:break;

            }

            $domainID = intval($_POST['domainID']);

            $res = Main::datChange($data, $domainID, $table);

            return $res;
        }
    }

    //添加备忘信息
    public function noticeAdd()
    {

        if (@!empty($_POST['notices']) && @!empty($_POST['domainID'])) {

            $noticesData = [];
            $noticesData['notice'] = htmlspecialchars(trim(json_decode($_POST['notices'],true)));
            $noticesData['domain_id'] = intval($_POST['domainID']);

            $notices = new Notices();
            $res = $notices -> dataInsert($noticesData);

            return $res;
        }

        return false;
    }

    //获取备忘信息列表
    public function noticeList($domainID, $pageNow = 1, $pageSize = 7)
    {

        $domainID = intval($domainID);
        $pageNow = intval($pageNow) > 0 ? intval($pageNow) : 1;
        $pageSize = intval($pageSize) > 0 ? intval($pageSize) : 7;

        $notices = new Notices();
        $res = $notices -> getList($domainID, $pageNow, $pageSize);

        $resNum = count($res)-1;
        for ($i = 0; $i < $resNum; $i++) {
            $res[$i]['notice_at'] = date('Y-m-d H:i:s',  $res[$i]['notice_at']);
        }

        return json_encode($res);

    }

    //删除备忘信息
    public function noticeDel()
    {
        if (@!empty($_POST['noticeID'])) {
            $noticeID = intval($_POST['noticeID']);
            $notice = new Notices();
            $res = $notice -> dataDel($noticeID);

            return $res;
        }

        return null;
    }

    //备忘信息修改
    public function noticeUpdate()
    {

        if (@!empty($_POST['noticeID'])) {
            $noticeID = intval($_POST['noticeID']);
            $noticeCnt = trim(json_decode($_POST['nNoticeCnt'],true));
            $notice = new Notices();
            $res = $notice -> updateCnt($noticeCnt, $noticeID);

            return $res;
        }

        return null;
    }


    //快速查找
    public function cntSearch()
    {
        if (@!empty($_POST['search_type'])){

            $type = trim($_POST['search_type']);

            $cnt = trim($_POST['search_cnt']);

            $searchData = Main::search($type, $cnt);

            return json_encode($searchData);
        }

        return null;
    }

    //excel批量导入数据
    public function excelUploadInsert()
    {
        \think\Loader::import('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();

        $file = request() -> file('file_datas');
        $fileInfo = $file -> validate(['ext' => 'xlsx,xls']) -> move(ROOT_PATH . 'public' . DS . 'uploads');

        if (empty($fileInfo)) {
            return false;
        }

        //读取表格数据
        //获取文件名
        $exclePath = $fileInfo -> getSaveName();
        //上传文件的地址
        $filename = ROOT_PATH . 'public' . DS . 'uploads'.DS . $exclePath;

        $extension = strtolower( pathinfo($filename, PATHINFO_EXTENSION) );

        \think\Loader::import('PHPExcel.IOFactory.PHPExcel_IOFactory');
        if ($extension =='xlsx') {
            $objReader = new \PHPExcel_Reader_Excel2007();
            $objExcel = $objReader ->load($filename);

        } else if ($extension =='xls') {

            $objReader = new \PHPExcel_Reader_Excel5();
            $objExcel = $objReader->load($filename);


        }
        $excelDatas = $objExcel -> getsheet(0) -> toArray();

        $filed = $excelDatas[0];
        $filedNum = count($filed);

        $dataRow = count($excelDatas);
        $datas = [];
        $tmp = [];
        for ($i = 1; $i < $dataRow; $i++) {
            for ($j = 0; $j < $filedNum; $j++) {
                $tmp[$filed[$j]] = $excelDatas[$i][$j];
            }
            array_push($datas,$tmp);
            $tmp = [];
        }

        $res = Main::dataImport($datas);

        return $res;
    }

    //txt 批量导入数据
    public function txtUploadInsert()
    {
        $filePath=$_FILES["file_datas"]["tmp_name"];
        if (!preg_match('/\.txt/i', $filePath))
            return false;
        $fp = fopen($filePath,'r');

        $i = 0;
        $tmpArr = [];
        $filed = [];
        $data = [];
        while (!feof($fp)) {
            $tmp = fgets($fp);
            $arr = array_values(array_filter(explode('	', $tmp)));

            if ($i < 1) {
                foreach ($arr as $key => $val) {
                    $tmpArr[] = trim($val);
                }
                $filed = $tmpArr;
            } else {
                $arrNum = count($arr);
                for ($k = 0; $k < $arrNum; $k++) {
                    $tmpArr[$filed[$k]] = trim($arr[$k]);
                }

                array_push($data,$tmpArr);
            }

            $tmpArr = [];

            $i+=1;
        }

        $data = array_values(array_filter($data));

        $res = Main::dataImport($data);

        return $res;
    }

    //获取导出数据表字段
    public function getImFiled()
    {

        if (isset($_POST['table']) && !empty($_POST['table'])) {
            $table = trim($_POST['table']);

            $tableData = self::str2tab($table);

            if (count($tableData['table']) > 0) {
                $imFileds = Main::importFiled($tableData['table']);
            } else {
                return false;
            }

            return json_encode($imFileds);
        }
    }

    //导出数据
    public function importData()
    {


        if (isset($_POST['table']) && !empty($_POST['table'])) {
            $table = trim($_POST['table']);
            $fields = json_decode($_POST['filedArr'], true);
            $tableData = self::str2tab($table);

            if (count($tableData['table']) > 0) {

                if ($table == 'all' && $fields[0] == 'all') {
                    $tableName = 'main_server_seo';
                    $fields = '';

                } elseif ($table == 'all' && $fields[0] != 'all') {
                    $tableName = 'main_server_seo';

                } elseif ($table != 'all' && $fields[0] == 'all') {
                    $fields = '';
                    $tableName = '';
                    foreach ($tableData['table'] as $key => $val) {
                        $tableName .= '_'.$val;
                    }
                    $tableName = preg_replace('/^_/','',$tableName);

                } elseif ($fields[0] != 'all' || $table != 'all') {
                    $tableName = '';
                    foreach ($tableData['table'] as $key => $val) {
                        $tableName .= '_'.$val;
                    }
                    $tableName = preg_replace('/^_/','',$tableName);

                }
                if (!preg_match('/_/',$tableName)) {
                    $tableName = 'web_'.$tableName;
                }

                $resData = self::getImpData($tableName,$fields);

                //导出excel
                \think\Loader::import('PHPExcel.PHPExcel');
                \think\Loader::import('PHPExcel.IOFactory.PHPExcel_IOFactory');
                \think\Loader::import('PHPExcel.Worksheet.PHPExcel_Worksheet');
                $objPHPExcel = new \PHPExcel();

                $objSheet = $objPHPExcel -> getActiveSheet();
                $objSheet ->getDefaultStyle()->getFont()->setName('Arial')->setSize(11);
                $objSheet->freezePane('C2');
                $borderStyle = [
                    'borders' => array(
                        'allborders' => array(
                            'style' => \PHPExcel_Style_Border::BORDER_THICK,
                            'color' => array('rgb' => '000000'),
                        ),
                    ),
                ];
                //PHPExcel_Style_Border::BORDER_THICK
                $numTitles = count($resData[0]);
                $arrColumn = self::getALLColumn($numTitles);
                $numData = count($resData);

                //输出title行
                $i = 0;
                foreach ($resData[0] as $key => $val) {
                    $columnP = self::getCellP($i);
                    $objSheet->setCellValue($columnP.'1',$resData[0][$key]);
                    $objSheet->getColumnDimension($columnP)->setWidth(25);
                    $i+=1;
                }
                $widthP = self::getCellP(0).'1:'.self::getCellP($numTitles-1).'1';


                $objSheet->getStyle($widthP)->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);


                $objSheet ->getStyle($widthP)
                    ->getFont()->setSize(18)->setBold(true);
                $objSheet->getStyle($widthP)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                $objSheet -> getStyle('A1:'.self::getCellP($numTitles-1).($numData))->applyFromArray($borderStyle);

                $objSheet->getRowDimension(1)->setRowHeight(45);

                for($i=1;$i<$numData;$i++){
                    $objSheet->getRowDimension(($i+1))->setRowHeight(33);
                    $j = 0;
                    foreach ($resData[$i] as $k => $val) {
                        $objSheet->setCellValue($arrColumn[$j].($i+1),$resData[$i][$k]);

                        $j+=1;
                    }

                }
                $cntArea = self::getCellP(0).'2:'.self::getCellP($numTitles-1).$numData;
                $objSheet -> getStyle($cntArea) -> getAlignment()
                    -> setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                    -> setVertical(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//                header('Content-Type: application/vnd.ms-excel');
//                header('Content-Disposition: attachment;filename="'.$tableData['cn_name'].'数据('.date('Ymd').').xls"');
//                header('Cache-Control: max-age=0');

                $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                $filePath ='/dataImport/'.$tableName.date('Ymd').'.xls';
//                $objWriter->save('php://output');
                $objWriter->save(WEB_PATH.$filePath);

                return $filePath;

            } else {
                return false;
            }


        }

        return false;
    }

    public static function getALLColumn($length){
        $arrColumn = [];
        for ($i=0;$i<$length;$i++){
            $arrColumn[] = self::getCellP($i);
        }

        return $arrColumn;
    }

    public static function getCellP($index){
        $AZlist = [];
        $Plist = range('A','Z');
        $AZlist = array_merge($AZlist,$Plist);
        for ($i = 0; $i < 26; $i++){
            for ($j = 0; $j < 26; $j++) {
                array_push($AZlist,$Plist[$i].$Plist[$j]);
            }
        }
        $index = intval($index);

        return $AZlist[$index];
    }

    //table参数转数据表
    public static function str2tab($tab)
    {
        $table = trim($tab);
        $tableData = [];
        switch ($table) {
            case 'all':
                $tableData = ['main', 'server', 'seo'];
                $tableNameCN = '网站列表_服务器_关键词';
                break;
            case 'm':
                $tableData = ['main'];
                $tableNameCN = '网站列表';
                break;
            case 'msv':
                $tableData = ['main', 'server'];
                $tableNameCN = '网站列表_服务器';
                break;
            case 'ms':
                $tableData = ['main', 'seo'];
                $tableNameCN = '网站列表_关键词';
                break;
            case 'sv':
                $tableData = ['server'];
                $tableNameCN = '服务器';
                break;
            case 's':
                $tableData = ['seo'];
                $tableNameCN = '关键词';
                break;
            default: break;

        }
        $res = [];
        $res['table'] = $tableData;
        $res['cn_name'] = $tableNameCN;
        return $res;
    }

    /**
     * @param $table
     * @param $fields
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getImpData($table, $fields)
    {
        $tableName = trim($table);
        $main = new Main();
        //导出数据字段完善
        if (preg_match('/^web_/',$tableName)){
            //表
            if ($tableName == 'web_main') {
                if (!empty($fields)) {
                    if (!in_array('domain_name', $fields)) {
                        array_unshift($fields, 'domain_name');
                    }
                    if (!in_array('domain', $fields)) {
                        array_unshift($fields, 'domain');
                    }
                }
            }

            if ($tableName == 'web_server') {
                $tableName = 'main_server';

                if (empty($fields)) {
                    $fields = [];
                    $tmpColumn = $main -> getColumn('web_server');
                    foreach ($tmpColumn as $k => $v) {
                        array_push($fields,$k);
                    }
                }

                array_unshift($fields, 'domain_name');
                array_unshift($fields, 'domain');

            }

            if ($tableName == 'web_seo') {
                $tableName = 'main_seo';

                if (empty($fields)) {
                    $fields = [];
                    $tmpColumn = $main -> getColumn('web_seo');
                    foreach ($tmpColumn as $k => $v) {
                        array_push($fields,$k);
                    }
                }

                array_unshift($fields, 'domain_name');
                array_unshift($fields, 'domain');

            }

        } else {
            //视图
            if (!empty($fields)){
                if (!in_array('domain_name', $fields)) {
                    array_unshift($fields, 'domain_name');
                }
                if (!in_array('domain', $fields)) {
                    array_unshift($fields, 'domain');
                }
            }
        }


        $column[0] = $main -> getColumn($tableName);

        foreach ($column[0] as $k => $val) {
            if ($k == 'domain_id' || $k == 'server_id' || $k == 'seo_id'){
                unset($column[0][$k]);
                continue;
            }
            if (!empty($fields)){
                if (!in_array($k, $fields)) {
                    unset($column[0][$k]);
                    continue;
                }
            }


        }

        $data = Db::table($tableName) -> field($fields) -> select();

        $dataNum = count($data);
        for ($i = 0; $i < $dataNum; $i++) {
            foreach ($data[$i] as $k => $v) {

                if ($k == 'domain_id' || $k == 'server_id' || $k == 'seo_id'){
                    unset($data[$i][$k]);
                    continue;

                }
                if ($k == 'web_lrat' || $k == 'web_srat' || $k == 'domain_rat' || $k == 'agent_rat') {
                    $data[$i][$k] = date('Y-m-d',$data[$i][$k]);
                    continue;
                }
                if (empty($data[$i][$k])) {
                    $data[$i][$k] = '暂无';
                    continue;
                }
            }
        }


        $resData = array_merge($column, $data);

        return $resData;
    }
}
