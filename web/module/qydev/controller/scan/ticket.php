<?php

/**
 * 微信ticket
 *
 * @author  wangl
 */
class ticket implements wx_ticket
{
    /**
     * 获取ticket
     *
     * @param   String  token
     *
     * @author  wangl
     */
    public function get($token)
    {
        if ( !$token ) {
            return '';
        }

        $info = _model('setting')->read(array('field' => 'js_ticket'));

        // 本地数据中存的有
        if ( $info ) {
            $val = json_decode($info['value'], true);

            // 并且时间没有过期
            if ( (time() - 5) > $val['expires_in'] && $val['token'] == $token ) {
                return $val['ticket'];
            }
        }

        // 接口获取
        $res = $this -> api_get($token);

        // 正确的调用应该有ticket和expires_in
        if ( !$res || empty($res['ticket']) || empty($res['expires_in']) ) {
            return '';
        }

        // 重置超时时间
        $res['expires_in'] += time();
        // 加入token
        $res['token'] = $token;

        // 修改
        if ( $info ) {
            _model('setting')->update(array('field' => 'js_ticket'), array('value' => json_encode($res)));
        // 添加
        } else{
            _model('setting')->create(array('field' => 'js_ticket', 'value' => json_encode($res)));
        }

        return $res['ticket'];
    }

    /**
     * 获取ticket
     *
     * @param   String  token
     *
     */
    private function api_get($token)
    {
        if ( !$token ) {
            return array();
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token='.$token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);

        $res = json_decode($res, true);

        if ( !$res ) {
            return array();
        }

        return $res;
    }
}
