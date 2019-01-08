<?php
namespace app\index\controller;

use app\index\model\Notices;
use app\index\model\Seo;
use app\index\model\Server;
use think\Controller;
use app\index\model\Main;
use think\Db;

class Index extends Controller
{
    public function index()
    {
        return $this -> fetch();
    }

    //网站列表
    public function webList($pageNow = 1, $pageSize = 12)
    {
        $web = new Main();

        $pageNow = intval($pageNow) > 0 ? intval($pageNow) : 1;
        $pageSize = intval($pageSize) > 0 ? intval($pageSize) : 12;

        $res = $web -> getList($pageNow, $pageSize);

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
                            if ($key=='id')
                                continue;

                            $serverData[$key] = $data[$key];
                        }
                    }
                    $server -> db() ->insert($serverData);

                   if ($s) {
                       $seoData = [];
                       $seo = new Seo();
                       $seoCol = $seo -> getColumn();
                       foreach ($seoCol as $key=>$value ){
                           if ($key=='seo_id')
                               continue;

                           $seoData[$key] = $data[$key];
                       }
                   }
                   $seo -> db() ->insert($seoData);
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

            return $domainID;

        }

       return null;
    }


    public function showMSVInfo($pageNow = 1, $pageSize = 12)
    {
        $server = new Server();
        $pageNow = intval($pageNow) > 1 ? intval($pageNow) : 1;
        $pageSize = intval($pageSize) > 1 ? intval($pageSize) : 1;
        $res = $server -> getMSVInfo($pageNow, $pageSize);

        return json_encode($res);
    }

    public function showMSInfo($pageNow = 1, $pageSize = 12)
    {
        $seo = new Seo();
        $pageNow = intval($pageNow) > 0 ? intval($pageNow) : 1;
        $pageSize = intval($pageSize) > 0 ? intval($pageSize) : 12;
        $res = $seo -> getMSInfo($pageNow, $pageSize);

        return json_encode($res);
    }

    public function showMSVSInfo($pageNow = 1, $pageSize = 12)
    {
        $main = new Main();
        $pageNow = intval($pageNow) > 0 ? intval($pageNow) : 1;
        $pageSize = intval($pageSize) > 0 ? intval($pageSize) : 12;
        $res = $main -> getMSVSInfo($pageNow, $pageSize);

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

    //删除
    public function webDel()
    {

        if (isset($_POST['domain_id']) || !empty($_POST['domain_id'])) {

           $res = Db::transaction(function (){
                $domainID = intval(trim($_POST['domain_id']));

                $seo = new Seo();
                $seo -> del($domainID, 'seo_id');

                $server = new Server();
                $server -> del($domainID, 'server_id');

                $main = new Main();
                $main -> del($domainID,'domain_id');

            });

           return $res;
        }

            return false;

    }

    public function update()
    {

        if (isset($_POST['field']) && !empty($_POST['field']) && isset($_POST['domainID']) && !empty($_POST['domainID'])){
            $data = json_decode($_POST['field'],true);
            $tab = trim($_POST['table']);

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


    public function noticeAdd()
    {

        if (@!empty($_POST['notices']) && @!empty($_POST['domainID'])) {

            $noticesData = [];
            $noticesData['notice'] = trim(json_decode($_POST['notices'],true));
            $noticesData['domain_id'] = intval($_POST['domainID']);

            $notices = new Notices();
            $res = $notices -> dataInsert($noticesData);

            return $res;
        }

        return false;
    }

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

    public function test($name = 'testname', $id = 20)
    {
        echo $name.'||'.$id;
    }
}
