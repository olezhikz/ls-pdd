<?php

class ModuleUser_EntityUser extends EntityORM
{
    protected $aValidateRules = array(
        array(
            'mail', 
            'email', 
            'allowEmpty' => false, 
            'on' => array('registration')
        ),
        
        [   
            'mail_login', 
            'string', 
            'on' => array('login'),
            'allowEmpty' => false
        ],
        [   
            'mail', 
            'mail_exists', 
            'on' => array('registration'),
            'allowEmpty' => false
        ],
        [
            'login', 
            'login_exists', 
            'on' => array('registration', 'add')
        ],
        [
            'login', 
            'change_login', 
            'on' => array('change_login', 'profile_settings')
        ],
        [
            'login', 
            'login', 
            'on' => array('registration', 'change_login', 'profile_settings', 'add')
        ],
        [   'name', 
            'string', 
            'min' => 3, 
            'max' => 200, 
            'on' => array( 'profile_settings')
        ],
        [   'name', 
            'string', 
            'min' => 3, 
            'max' => 200, 
            'allowEmpty' => false,
            'on' => array('registration', 'add')
        ],
        [   
            'role', 
            'string', 
            'allowEmpty' => false,
            'on' => array('registration', 'add'), 
            'msg' => 'auth.registration.notices.role_no_valid'
        ],
        [   
            'role', 
            'role_exists', 
            'on' => array('registration', 'add')
        ],
        [   
            'phone', 
            'string',
            'max' => 20,
            'on' => array( 'profile_settings', 'add')
        ],
        [   
            'site', 
            'url',
            'max' => 500,
            'on' => array( 'profile_settings', 'add')
        ],
        [   
            'address', 
            'string',
            'max' => 500,
            'on' => array( 'profile_settings', 'add')
        ],
        [   
            'about', 
            'string',
            'max' => 1000,
            'on' => array( 'profile_settings', 'add')
        ],
        [   
            'photo_count', 
            'number',
            'max' => 1,
            'on' => array( 'profile_settings', 'add')
        ]
    );

    protected $aRelations = array(
        'session' => array(self::RELATION_TYPE_HAS_ONE, 'ModuleUser_EntitySession', 'user_id'),
    );


    /**
     * Определяем дополнительные правила валидации
     *
     * @param array|bool $aParam
     */
    public function __construct($aParam = false)
    {
       
        parent::__construct($aParam);
        
        if(Config::Get('module.user.captcha_use_registration')){
            $this->aValidateRules[] = array(
                'recaptcha',
                'captcha_recaptcha',
                'name'  => 'user_signup',
                'on'    => array('registration'),
                'label' => $this->Lang_Get('auth.labels.captcha_field'),
                'msg'   => $this->Lang_Get('talk.response.notice.error_captcha')
            );
        }
        
        $this->aValidateRules[] = array(
            'password', 
            'string', 
            'min' => 10,
            'max' => 50,
            'msg' => $this->Lang_Get('auth.registration.notices.password_no_valid', ['min' => 10, 'max' => 50]),
            'on' => array('registration', 'login'),
            'allowEmpty' => false
        );
    }
    
    public function ValidateLogin($sValue, $aParams)
    {
        if ($this->User_CheckLogin($sValue)) {
            return true;
        }
        return $this->Lang_Get('auth.registration.notices.error_login');
    }
   

    /**
     * Проверка емайла на существование
     *
     * @param string $sValue Валидируемое значение
     * @param array $aParams Параметры
     * @return bool
     */
    public function ValidateMailExists($sValue, $aParams)
    {
        if (!$this->User_GetUserByMail($sValue)) {
            return true;
        }
        return $this->Lang_Get('auth.registration.notices.error_mail_used');
    }
    
    public function ValidateRoleExists($sValue, $aParams)
    {
        if ($this->Rbac_GetRoleByFilter(['code' => $sValue])) {
            return true;
        }
        return $this->Lang_Get('auth.registration.notices.role_no_exists', ['role' => $sValue]);
    }
    
    public function ValidateLoginExists($sValue, $aParams)
    {
        $aPages = array_keys( Config::Get('router.page') ); 
        if(in_array($sValue, $aPages)){
            return $this->Lang_Get('auth.registration.notices.error_login_used');
        }
        
        if (!$this->User_GetUserByLogin($sValue)) {
            return true;
        }
        
        
        return $this->Lang_Get('auth.registration.notices.error_login_used');
    }

    public function ValidateChangeLogin($sValue, $aParams)
    {
        if($sValue == $this->_getOriginalDataOne('login')){
            return true;
        }
        
        return $this->ValidateLoginExists($sValue, $aParams);
    }

    protected function beforeSave()
    {
        if ($this->_isNew()) {
            if (!$this->getDateCreate()) {
                $this->setDateCreate(date("Y-m-d H:i:s"));
            }
            if (!$this->getIpCreate()) {
                $this->setIpCreate(func_getIp());
            }
        }
        return true;
    }

    public function isAdministrator()
    {
        return $this->isAdmin();
    }

    public function isAdmin()
    {
        return $this->getIsAdmin();
    }
    
    public function isAnoname() {
        return ($this->getId() == 0);
    }

    public function getUrl($sPage = null)
    {
        return '#';
        return Router::GetPath('profile') . $this->getId() . '/' . ($sPage ? $sPage . '/' : '');
    }

    public function getDisplayName()
    {
        return htmlspecialchars($this->getMail());
    }

    /**
     * Возвращает статус онлайн пользователь или нет
     *
     * @return bool
     */
    public function isOnline()
    {
        if ($oSession = $this->getSession()) {
            if (time() - strtotime($oSession->getDateLast()) < Config::Get('module.user.time_onlive')) { // 10 минут
                return true;
            }
        }
        return false;
    }

    public function getProfileUrl() { 
        return Router::GetPath($this->getLogin()?$this->getLogin():'profile/'.$this->getId());
    }
    
    public function afterSave() {
        parent::afterSave();
        if(!$this->isRole($this->getRole())){
            $this->Rbac_AddRoleToUser($this->getRole(), $this->getId());
        }
    }
    
    
    public function getProfilePhoto() {
        return $this->User_GetDefaultPhoto();
    }
    
    public function getProfileAvatar($size = 'x100') {
        return $this->User_GetDefaultAvatar();
    }
    
    public function isRole($sRole) {
        if(!$this->getRoles()){
            $this->setRoles($this->Rbac_GetRolesByUser($this));
        }
        
        foreach ($this->getRoles() as $oRole) {
            if($oRole->getCode() == $sRole){
                return true;
            }
        }
        return false;
    }
    
    
    public function isAllow($sPermissionCode, $aParamsOrPlugin = array(), $sPluginOrParams = null) {
        return $this->Rbac_IsAllowUser($this, $sPermissionCode, $aParamsOrPlugin , $sPluginOrParams );
    }
    
    public function getRating() {
        return round($this->Rating_GetRatingUser($this->getId()),1);
    }
    
    public function getCountRated() {
        return $this->Rating_GetCountVoteTarget('user', $this->getId());
    }
    
    public function getRatingStat() {
        $aStat = $this->Rating_GetRatingStatTarget('user', $this->getId());
        return $aStat;
    }
    
}