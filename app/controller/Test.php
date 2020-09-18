<?php
namespace app\controller;

use app\BaseController;
use upacp\sdk\AcpService;
use upacp\sdk\SDKConfig;

class Test extends BaseController
{
    public function index()
    {
        $params = array(
            //以下信息非特殊情况不需要改动
            'version' => SDKConfig::getSDKConfig()->version,                 //版本号
            'encoding' => 'utf-8',				  //编码方式
            'txnType' => '01',				      //交易类型
            'txnSubType' => '01',				  //交易子类
            'bizType' => '000201',				  //业务类型
            'frontUrl' =>  SDKConfig::getSDKConfig()->frontUrl,  //前台通知地址
            'backUrl' => SDKConfig::getSDKConfig()->backUrl,	  //后台通知地址
            'signMethod' => SDKConfig::getSDKConfig()->signMethod,	              //签名方法
            'channelType' => '08',	              //渠道类型，07-PC，08-手机
            'accessType' => '0',		          //接入类型
            'currencyCode' => '156',	          //交易币种，境内商户固定156

            //TODO 以下信息需要填写
            'merId' => '777290058110048',		//商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
            'orderId' => 'R'.date('yymdhis').rand(10000,99999),	//商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
            'txnTime' => date('yymdhis'),	//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
            'txnAmt' => 1,	//交易金额，单位分，此处默认取demo演示页面传递的参数

            // 请求方保留域，
            // 透传字段，查询、通知、对账文件中均会原样出现，如有需要请启用并修改自己希望透传的数据。
            // 出现部分特殊字符时可能影响解析，请按下面建议的方式填写：
            // 1. 如果能确定内容不会出现&={}[]"'等符号时，可以直接填写数据，建议的方法如下。
            //    'reqReserved' =>'透传信息1|透传信息2|透传信息3',
            // 2. 内容可能出现&={}[]"'符号时：
            // 1) 如果需要对账文件里能显示，可将字符替换成全角＆＝｛｝【】“‘字符（自己写代码，此处不演示）；
            // 2) 如果对账文件没有显示要求，可做一下base64（如下）。
            //    注意控制数据长度，实际传输的数据长度不能超过1024位。
            //    查询、通知等接口解析时使用base64_decode解base64后再对数据做后续解析。
            //    'reqReserved' => base64_encode('任意格式的信息都可以'),

            //TODO 其他特殊用法请查看 pages/api_05_app/special_use_purchase.php
        );
         AcpService::sign($params);
        $url = SDKConfig::getSDKConfig()->appTransUrl;
        $result_arr = AcpService::post ($params,$url);
        if (!AcpService::validate ($result_arr) ){
            echo "应答报文验签失败<br>\n";
            return;
        }
        var_dump($result_arr);
        if ($result_arr["respCode"] == "00"){
            //成功
            //TODO
            echo "成功接收tn：" . $result_arr["tn"] . "<br>\n";
            echo "后续请将此tn传给手机开发，由他们用此tn调起控件后完成支付。<br>\n";
            echo "手机端demo默认从仿真获取tn，仿真只返回一个tn，如不想修改手机和后台间的通讯方式，【此页面请修改代码为只输出tn】。<br>\n";
        } else {
            //其他应答码做以失败处理
            //TODO
            echo "失败：" . $result_arr["respMsg"] . "。<br>\n";
        }
    }

    public function query()
    {
        $params = array(
            //以下信息非特殊情况不需要改动
            'version' => SDKConfig::getSDKConfig()->version,		  //版本号
            'encoding' => 'utf-8',		  //编码方式
            'signMethod' =>SDKConfig::getSDKConfig()->signMethod,		  //签名方法
            'txnType' => '00',		      //交易类型
            'txnSubType' => '00',		  //交易子类
            'bizType' => '000000',		  //业务类型
            'accessType' => '0',		  //接入类型
            'channelType' => '07',		  //渠道类型

            //TODO 以下信息需要填写
            'merId' => '777290058110048',		//商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
            'orderId' => 'R20200917123456',	//商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
            'txnTime' => '20200917165703',	//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
        );


            AcpService::sign ( $params ); // 签名
        $url =SDKConfig::getSDKConfig()->singleQueryUrl;

        $result_arr =AcpService::post ( $params, $url);
        if(count($result_arr)<=0) { //没收到200应答的情况
            printResult ( $url, $params, "" );
            return;
        }
    dd($result_arr);
        printResult ($url, $params, $result_arr ); //页面打印请求应答数据

        if (!
            AcpService::validate ($result_arr) ){
            echo "应答报文验签失败<br>\n";
            return;
        }

        echo "应答报文验签成功<br>\n";
        if ($result_arr["respCode"] == "00"){
            if ($result_arr["origRespCode"] == "00"){
                //交易成功
                //TODO
                echo "交易成功。<br>\n";
            } else if ($result_arr["origRespCode"] == "03"
                || $result_arr["origRespCode"] == "04"
                || $result_arr["origRespCode"] == "05"){
                //后续需发起交易状态查询交易确定交易状态
                //TODO
                echo "交易处理中，请稍微查询。<br>\n";
            } else {
                //其他应答码做以失败处理
                //TODO
                echo "交易失败：" . $result_arr["origRespMsg"] . "。<br>\n";
            }
        } else if ($result_arr["respCode"] == "03"
            || $result_arr["respCode"] == "04"
            || $result_arr["respCode"] == "05" ){
            //后续需发起交易状态查询交易确定交易状态
            //TODO
            echo "处理超时，请稍微查询。<br>\n";
        } else {
            //其他应答码做以失败处理
            //TODO
            echo "失败：" . $result_arr["respMsg"] . "。<br>\n";
        }
    }
}
