<?php

/**
 * alltosun.com  dbWrapper.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2012-9-15 下午9:10:24 $
 * $Id: db.php 954 2015-01-20 02:15:30Z shiwh $
*/

class AnOpenApiDbWrapper extends AnOpenApiConnectAbstract implements AnOpenApiConnectWrapper
{
    public function checkAuth($scope = '')
    {
        if ($this->site_name == 'qqweibo' || $this->site_name == 'qqsns') {
            return $this->qqCheckAuth();
        } else if($this->site_name == 'weixin'){
            return $this->weixinCheckAuth($scope);
        } else if($this->site_name == 'bluemp') {
            return $this->bluempCheckAuth();
        } else {
            return $this->commonCheckAuth();
        }
    }

    private function qqCheckAuth()
    {
        if (!$this->user_id) {
            return 'no';
        }

        if (!empty($_SESSION['t_access_token']) && time() < $_SESSION['t_expires_time']) {
            return 'ok';
        } else {
            $connect_info = _uri('connect_qq', array('user_id'=>$this->user_id, 'connect_site_id'=>$this->site_id));

            if (!$connect_info) {
                return 'no';
            }

            if (time() > $connect_info['expires_time']) {
                return 'expired';
            }

            $_SESSION['t_access_token']  = $connect_info['access_token'];
            $_SESSION['t_refresh_token'] = $connect_info['refresh_token'];
            //$_SESSION['t_expire_in']     = $connect_info['expires_in'];
            $_SESSION['t_expires_time']  = $connect_info['expires_time'];
            $_SESSION['t_openid']        = $connect_info['connect_open_id'];

            return 'ok';
        }
    }

    private function weixinCheckAuth($scope)
    {
        if (!$this->user_id) {
            return 'no';
        }

        if (!empty($_SESSION['weixin_access_token']) && time() < $_SESSION['weixin_expires_time'] && $_SESSION['weixin_scope'] == $scope ) {
            return 'ok';
        } else {
            $connect_info = _uri('connect_weixin', array(
                            'user_id'=>$this->user_id,
                            'connect_site_id'=>$this->site_id,
                            'scope'=>$scope
            ));

            if (!$connect_info) {
                return 'no';
            }

            if (time() > $connect_info['expires_time']) {
                return 'expired';
            }

            $_SESSION['weixin_openid']        = $connect_info['connect_open_id'];
            $_SESSION['weixin_access_token']  = $connect_info['access_token'];
            $_SESSION['weixin_refresh_token'] = $connect_info['refresh_token'];
            // $_SESSION['weixin_expires_in']    = $connect_info['expires_in'];
            $_SESSION['weixin_expires_time']  = $connect_info['expires_time'];
            // add scope
            $_SESSION['weixin_scope']         = $connect_info['scope'];

            return 'ok';
        }
    }

    private function commonCheckAuth()
    {
        if (!$this->user_id) {
            return 'no';
        }

        if (isset($_SESSION[$this->site_name]['token']['access_token']) && isset($_SESSION[$this->site_name]['token']['expires_time']) && time() < $_SESSION[$this->site_name]['token']['expires_time']) {
            return 'ok';
        } else {
            $connect_info = _uri('connect', array('user_id'=>$this->user_id, 'connect_site_id'=>$this->site_id));
            if (!$connect_info) {
                return 'no';
            }

            if (time() > $connect_info['expires_time']) {
                return 'expired';
            }

            $_SESSION[$this->site_name]['token']['access_token'] = $connect_info['access_token'];
            $_SESSION[$this->site_name]['token']['expires_time'] = $connect_info['expires_time'];
            // $_SESSION[$this->site_name]['connect_user_id'] = $connect_info['connect_user_id'];

            return 'ok';
        }
    }

    public function saveAuth($token)
    {
        if ($this->site_name == 'qqweibo' || $this->site_name == 'qqsns') {
            return $this->qqSaveAuth($token);
        } else if ($this->site_name == 'weixin') {
            return $this->weixinSaveAuth($token);
        } else {
            return $this->commonSaveAuth($token);
        }
    }

    private function qqSaveAuth($token)
    {
        $_SESSION['t_openid']        = $token['openid'];
        $_SESSION['t_access_token']  = $token['access_token'];
        $_SESSION['t_refresh_token'] = $token['refresh_token'];
        $_SESSION['t_expires_in']    = $token['expires_in'];
        $_SESSION['t_expires_time']  = $token['expires_in'] + time();

        $connect_info = _uri('connect_qq', array('connect_open_id'=>$token['openid'], 'connect_site_id'=>$this->site_id));
        if (!$connect_info) {
            // 当前用户信息
            // $user_info = json_decode(Tencent::api('user/info'), true);
            $connect_info = array(
                    'user_id'           => $this->user_id,
                    'connect_open_id'   => $token['openid'],
                    'connect_site_id'   => $this->site_id,
                    'connect_user_name' => $token['name'],
                    'connect_nick_name' => $token['nick'],
                    'access_token'      => $token['access_token'],
                    'refresh_token'     => $token['refresh_token'],
                    'expires_time'      => $token['expires_in'] + time()
            );
            $connect_id = _model('connect_qq')->create($connect_info);

            $connect_info['id'] = $connect_id;

            return $connect_info;
        } else {
            $info = array(
                'access_token'=>$token['access_token'],
                'expires_time'=>$token['expires_in'] + time()
            );
            _model('connect_qq')->update($connect_info['id'], $info);

            $connect_info = _uri('connect_qq', $connect_info['id']);

            return $connect_info;
        }
    }

    /**
     * 微信保存授权
     * @param $token
     * @return array connect_info
     */
    private function weixinSaveAuth($token)
    {
        if(!$token) {
            throw new AnException("保存授权信息失败");
        }

        $time = time();
        $token['scope'] = trim(trim($token['scope']), ',');

        $_SESSION['weixin_openid']        = $token['openid'];
        $_SESSION['weixin_access_token']  = $token['access_token'];
        $_SESSION['weixin_refresh_token'] = $token['refresh_token'];
        $_SESSION['weixin_expires_in']    = $token['expires_in'];
        $_SESSION['weixin_expires_time']  = $token['expires_in'] + $time;
        // add scope
        $_SESSION['weixin_scope']         = $token['scope'];

        $connect_info = _uri('connect_weixin', array(
                        'connect_open_id'=>$token['openid'],
                        'connect_site_id'=>$this->site_id,
                        'scope' => $token['scope']
        ));
        if (!$connect_info) {
            // 当前用户信息
            $connect_info = array(
                            'user_id'           => $this->user_id,
                            'connect_open_id'   => $token['openid'],
                            'connect_site_id'   => $this->site_id,
                            //'connect_nick_name' => 'weixinOauth_' . substr($token['openid'], 0, 5),
                            'access_token'      => $token['access_token'],
                            'refresh_token'     => $token['refresh_token'],
                            'expires_time'      => $token['expires_in'] + $time,
                            'scope'             => $token['scope']
            );
            $connect_id = _model('connect_weixin')->create($connect_info);

            $connect_info['id'] = $connect_id;
            return $connect_info;
        } else {
            $info = array(
                            'access_token'=> $token['access_token'],
                            'expires_time'=> $token['expires_in'] + $time,
                            'scope'       => $token['scope']
            );
            _model('connect_weixin')->update($connect_info['id'], $info);

            $connect_info = array_merge($connect_info, $info);
            return $connect_info;
        }
    }

    private function commonSaveAuth($token)
    {
        $token['expires_time'] = $token['expires_in']+time();

        $_SESSION[$this->site_name]['token']['expires_time'] = $token['expires_time'];
        $_SESSION[$this->site_name]['token']['access_token'] = $token['access_token'];
        // $_SESSION[$this->site_name]['connect_user_id'] = $token['uid'];

        $connect_info = _uri('connect', array('connect_user_id'=>$token['uid'], 'connect_site_id'=>$this->site_id));
        if (!$connect_info) {
            // 创建一条记录，创建用户后，更新user_id字段
            $connect_info = array(
                    'user_id'           => $this->user_id,
                    'connect_user_id'   => $token['uid'],
                    'user_name'         => $token['user_name'],
                    'connect_site_id'   => $this->site_id,
                    'access_token'      => $token['access_token'],
                    'expires_time'      => $token['expires_time'],
                    'refresh_token'     => ''
            );
            $connect_id = _model('connect')->create($connect_info);

            $connect_info['id'] = $connect_id;

            return $connect_info;
        } else {
            _model('connect')->update($connect_info['id'], array('user_name'=>$token['user_name'], 'access_token'=>$token['access_token'], 'expires_time'=>$token['expires_time'], 'is_bind'=>1));

            $connect_info = _uri('connect', $connect_info['id']);
            return $connect_info;
        }
    }

    public function getAccessToken()
    {
        $access_token = '';

        if (empty($_SESSION[$this->site_name]['token']['access_token'])) {
            $connect_info = _uri('connect', array('user_id'=>$this->user_id, 'connect_site_id'=>$this->site_id));
            if (!$connect_info) {
                return $access_token;
            }
            $_SESSION[$this->site_name]['token']['expires_time'] = $connect_info['expires_time'];
            $_SESSION[$this->site_name]['token']['access_token'] = $connect_info['access_token'];
            $access_token = $connect_info['access_token'];
        } else {
            $access_token = $_SESSION[$this->site_name]['token']['access_token'];
        }

        return $access_token;
    }
}
?>