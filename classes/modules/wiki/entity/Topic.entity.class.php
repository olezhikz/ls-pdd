<?php

class PluginWiki_ModuleWiki_EntityTopic extends EntityORM
{
    protected $aBehaviors = array(
        /**
         * Дополнительные поля
         */
        'property' => 'ModuleProperty_BehaviorEntity',
        'category' => [
            'class' => 'ModuleCategory_BehaviorEntity',
            'target_type' => 'wiki'
        ]
    );
    
    public function getUrl($bAbsolute = true) {
        $oCategory = $this->category->getCategory();
        if($oCategory){
            return Router::GetPath('wiki/'.$oCategory->getUrlFull());
        }
        return parent::getUrl($bAbsolute);
    }
    
    public function _getPrimaryKeyValue() {
        return $this->_getDataOne('topic_id');
    }
   
   
}