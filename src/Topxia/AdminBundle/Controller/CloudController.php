<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Topxia\Service\Util\CloudClientFactory;

class CloudController extends BaseController
{
    public function billAction(Request $request)
    {

        $factory = new CloudClientFactory();
        $client = $factory->createClient();

        $result = $client->getBills($client->getBucket());

        $loginToken = $this->getAppService()->getLoginToken();

        if (!empty($result['error'])) {
            return $this->createMessageResponse('error', '获取账单信息失败，云视频参数配置不正确，或网络通讯失败。',  '获取账单信息失败');
        }


        return $this->render('TopxiaAdminBundle:Cloud:bill.html.twig', array(
            'money' => $result['money'],
            'bills' => $result['bills'],
            'token' => $loginToken["token"]
        ));

    }

    public function rechargeAction(Request $request)
    {
        $loginToken = $this->getAppService()->getLoginToken();
        return $this->redirect('http://open.edusoho.com/token_login?token={$loginToken}&goto=order_recharge');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }
}