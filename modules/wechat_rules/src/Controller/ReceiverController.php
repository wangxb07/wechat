<?php

namespace Drupal\wechat_rules\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ReceiverController.
 */
class ReceiverController extends ControllerBase {

  /**
   * Receive.
   *
   * @return string
   *   Return receive string.
   */
  public function receive(Request $request) {
//    $signature = $request->get("signature", false);
//    $nonce = $request->get("nonce", false);
//    $echostr = $request->get("echostr", false);
//     // signature validate
//    if ($signature && $nonce && $echostr) {
//      if (weixin_platform_signature_validation($_GET["signature"], $_GET["timestamp"], $_GET["nonce"], $settings)) {
//        echo $_GET['echostr'];
//      }
//      else {
//        echo 'fail';
//      }
//      return;
//    }
//
//    $xml = $request->getContent();
//    $data = weixin_platform_xml_parse($xml);
//
//    watchdog('weixin_platform', 'weixin msg received: <pre>@data</pre>', array('@data' => print_r($data, true)));
//
//    if (isset($data['MsgType'])) {
//      // msg type content handle
//      module_invoke_all('weixin_platform_message_received', $settings, $data);
//
//      if (isset($data['Event'])) {
//        $event = trim($data['Event']);
//        // event type content handle
//        // todo body process 暂时不支持非事件类型的推送，考虑是否需要支持
//        $event_body = weixin_platform_event_body_process($data, $event);
//
//        if ($event_body) {
//          module_invoke_all('weixin_platform_event_received', $settings, $event_body, $event);
//        }
//      }
//    }
//    echo '';
  }
}
