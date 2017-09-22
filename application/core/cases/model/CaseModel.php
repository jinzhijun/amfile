<?php
namespace core\cases\model;

use core\Model;
use think\Request;
use core\cases\logic\CountryLogic;
use core\cases\logic\CaseLogic;
use core\cases\logic\CompanyLogic;
class CaseModel extends Model
{

    /**
     * 去前缀表名
     *
     * @var unknown
     */
    protected $name = 'cases_case';

    /**
     * 自动写入时间戳
     *
     * @var unknown
     */
    protected $autoWriteTimestamp = true;

    /**
     * 新增时自动完成
     *
     * @var array
     */
    protected $insert = [
        'case_code'
    ];
        /**
     * 更新时自动完成
     *
     * @var array
     */
    protected $update = [
        'case_code'
    ];
/*
 * 定义别名变量
 */
   public $alias_name='a_case';
   
   /*
    * 获取全部管理case
    */
   public function getCaseList($map=null){
        $alias=$this->alias_name; //case表别名
        $aliastype=CaseTypeModel::getInstance()->alias_name; //类型表别名
        $counry=CountryModel::getInstance()->alias_name; //国家表别名
        $province=AreaModel::getInstance()->alias_name[0];  //省
        $city=AreaModel::getInstance()->alias_name[1];   //市
        $district=AreaModel::getInstance()->alias_name[2]; //区
        $user=ChatUserModel::getInstance()->alias_name; //用户
        $status=CaseStatusModel::getInstance()->alias_name; //状态
        $ks= KsModel::getInstance()->alias_name;//科室
        $case_list = $this->withCates()->field(
                $alias.'.*,'
                .$aliastype.'.typename,'
                .$counry.'.name as country_name,'.$province.'.area_name as province_name ,'.$city.'.area_name as city_name ,'.$district.'.area_name as district_name ,'
                .$user.'.user_name as case_username , '.$user.'.avatar as user_avatar , '
                .$status.'.color as statuscolor ,'.$status.'.name as statusname , '
                .$ks.'.ks_name ,'.$ks.'.ks_ename '
                )->where($map)
            ->order($status.'.sort desc, '.$alias.'.sort desc,'.$alias.'.create_time desc');
        
        
        return $case_list;
   }
    /**
     * 使用别名
     *
     * @param unknown $query            
     */
    public function useAlias()
    {
        return $this->alias($this->alias_name);
    }
           /**
     * 关联监听组
     *
     * @return \think\model\relation\BelongsToMany
     */
    public function jtarr()
    {
        return $this->belongsToMany(UserModel::class, JtModel::getInstance()->getTableShortName(), 'user_id', 'cases_id');
    }
   /**
     * 连接分类
     *
     * @return \think\db\Query
     */
    public function withCates()
    {
        $query = $this->useAlias();
        $query=$this->joinCates($query);//加入分类
        $query= $this->withUser($query);//加入用户
        $query=$this->joinCountry($query);//加入国家
        $query= $this->joinStatus($query); //加入状态
        $query= $this->joinKs($query);  //加入科室
        return $this->joinAddress($query);
    }
    
           /**
     * 连接科室
     *
     * @return \think\db\Query
     */
    protected function joinKs($query)
    {
        $ks= KsModel::getInstance();
        return $query->join($ks->getTableShortName() . ' '.$ks->alias_name, $this->alias_name.'.ks_type = '.$ks->alias_name.'.ks_id');
    }  
         /**
     * 连接监听组
     *
     * @return \think\db\Query
     */
    protected function joinjt($query)
    {
        $jt= JtModel::getInstance();
        $user= UserModel::getInstance();
        return $query->join(JtModel::getInstance()->getTableShortName() .' '.$jt->alias_name,$this->alias_name.'.id ='.$jt->alias_name.'.cases_id')
            ->join(JtModel::getInstance()->getTableShortName().' '.$jt->alias_name,$user->alias_name. '.id ='.$jt->alias_name.'.user_id');
    }
   /**
     * 连接状态
     *
     * @return \think\db\Query
     */
    public function joinStatus($query)
    {
        $casestatus= CaseStatusModel::getInstance();
        return $query->join($casestatus->getTableShortName() . ' '.$casestatus->alias_name, $this->alias_name.'.case_status = '.$casestatus->alias_name.'.id');
    }
    /**
     * 连接提交case用户
     *
     * @return \think\db\Query
     */
     public function withUser($query)
    {
       $user=ChatUserModel::getInstance();
       return $query->join($user->getTableShortName() . ' '.$user->alias_name, $this->alias_name.'.userid = '.$user->alias_name.'.id');
    }
    /**
     * 连接分类
     *
     * @return \think\db\Query
     */
    public function joinCates($query)
    {
        $casetype=CaseTypeModel::getInstance();
        return $query->join($casetype->getTableShortName() . ' '.$casetype->alias_name, $this->alias_name.'.case_type = '.$casetype->alias_name.'.id');
    }

    /**
     * 连接国家
     *
     * @return \think\db\Query
     */
    public function joinCountry($query)
    {
        $casetype=CountryModel::getInstance();
        return $query->join($casetype->getTableShortName() . ' '.$casetype->alias_name, $this->alias_name.'.country = '.$casetype->alias_name.'.id');
    }
    
       /**
     * 连接省市区
     *
     * @return \think\db\Query
     */
    public function joinAddress($query)
    {
        $casetype=AreaModel::getInstance();
        return $query->join($casetype->getTableShortName() . ' '.$casetype->alias_name[0], $this->alias_name.'.province = '.$casetype->alias_name[0].'.id')
                     ->join($casetype->getTableShortName() . ' '.$casetype->alias_name[1], $this->alias_name.'.city = '.$casetype->alias_name[1].'.id')
                     ->join($casetype->getTableShortName() . ' '.$casetype->alias_name[2], $this->alias_name.'.district = '.$casetype->alias_name[2].'.id');
    }
    
    
 
    /**
     * 自动设置caseId
     *
     * @return string
     */
    protected function setCaseCodeAttr($value=null)
    {
        
        $request=\think\Request::instance();
        $id=Request::instance()->param('id');
        if(!$id){
            
       
        $field=$request->param();
        $countryid=$field['country'];
        $userid=$field['userid'];
        
       
        
        
      
        return $this->getNewCaseKey($countryid,$userid);
      
        }
        
    }

    /**
     * 获取一个新的CaseID
     *
     * @return string
     */
    public function getNewCaseKey($countryid,$userid)
    {
       $request = Request::instance();
       
      //查询国家信息
        $countrymap=[
            'id'=>$countryid
        ];
        $country=CountryLogic::getInstance()->getCountryList($countrymap, 1);
        $country_abbreviation=$country['abbreviation'];
        
        //获取case总数(包含未删除)
        $count=CaseLogic::getInstance()->getCaseCount([],1);
        
        /*
         * 获取公司简称
         */
        //获取用户所在公司
        $usermap=[
            'id'=>$userid
        ];
        $companyid=ChatUserModel::getInstance()->where($usermap)->value('company');
        if($companyid){
          //获取公司信息 
         $companymap=[
             'id'=>$companyid
         ];
         $company=CompanyLogic::getInstance()->getCompanyList($companymap, 1);
         $company_abbreviation=$company['abbreviation'];
         
        for ($index = 1; $index < 100; $index++) {
            $caseid='';
            $casemap=[];
            
            $caseid=$country_abbreviation.sprintf("%'X6s", $count+$index).$company_abbreviation;
            
            $casemap=[
                'case_code'=>$caseid
            ];
            $casecount=CaseLogic::getInstance()->getCaseCount($casemap,1);
            if(!$casecount){
                return $caseid;
                break;
            }
        }
       }else{
           exit;
       } 
       
    }
    

//    
//        /**
//     * 自动设置文章key
//     *
//     * @return string
//     */
//    protected function setCaseCodeAttr($value=null)
//    {
//        
//        $request=\think\Request::instance();
//        $id=Request::instance()->param('id');
//        $field=$request->param('field');
//        if(!$id){
//             $status=0;
//        }else{
//            if($field){
//                 $map=[
//                     'id'=>$id,
//                      'delete_time'=>0
//                 ];
//                 $code = $this->where($map)->find();
//                 return $code['case_code'];
//                 exit;
//             }else{
//                 $status=1;
//             }
//           
//        }
//        
//      
//            return $this->getNewCaseKey($value,$status);
//      
//           
//        
//    }

//    /**
//     * 获取一个新的文章Key
//     *
//     * @return string
//     */
//    public function getNewCaseKey($value=null,$status=0)
//    {
//       $request = Request::instance();
//       
//   
//      if($value){
//              $articleKey=$value;
//              $type=1;
//        }else{ 
//            
//                 $articleKey=$this->gethtime();
//                 $type=2;
//             
//              
//         }
//      if($status==0){
//          $map = [
//            'case_code' => $articleKey,
//            'delete_time'=>0
//           ];
//      }else{
//          $map = [
//            'case_code' => $articleKey,
//            'delete_time'=>0,
//              'id'=>['neq',Request::instance()->param('id')]
//           ];
//      }
//        
//        $record = $this->where($map)->find();
//        if (empty($record)) {
//            return $articleKey;
//        } else {
//            if($type==2){
//                return $this->getNewCaseKey();
//            }else{
//                $this->error('caseId已存在请重新添加');
//            }
//            
//        }
//       
//    }
//    
//    /*
//     * 获取当前毫秒时间戳
//     */
//    public function gethtime(){
//         $articleKey = microtime();
//               list($s1, $s2) = explode(' ', $articleKey);		
//         return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
//    }

}