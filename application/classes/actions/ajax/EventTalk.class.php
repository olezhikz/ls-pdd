<?php

/**
 * Description of ActionProfile_EventSettings
 *
 * @author oleg
 */
class ActionAjax_EventTalk extends Event {
    
    public $oUserCurrent;


    public function Init() {
        
    }
    
    public function EventAjaxResponseCreate() {
        
        $oResponse = Engine::GetEntity('Talk_Message', ['type' => 'response']);
        $oResponse->_setDataSafe($_REQUEST);
        
        $oResponse->setState('moderate');
        
        if($oResponse->_Validate()){
            if($oResponse->Save()){
                $this->Media_AttachMedia(getRequest('photos'), 'response', $oResponse->getId());
                
                $this->Viewer_AssignAjax('sUrlRedirect', getRequest('redirect'));
                
                $this->Message_AddNotice($this->Lang_Get('talk.response.notice.success_add'));
            }else{
                $this->Message_AddError($this->Lang_Get('common.error.error'));
            }
        }else{
            foreach ($oResponse->_getValidateErrors() as $aError) {
                $this->Message_AddError(array_shift($aError));
            }
        }
    }
    
    
    public function EventAjaxResponseEdit() {
        
        if(!$oResponse = $this->Talk_GetResponseByFilter(['id' => getRequest('id')])){
            $oResponse = Engine::GetEntity('Talk_Response');
            $oResponse->setState('moderate');
            $oResponse->_setValidateScenario('create');
        }
        
        $oResponse->_setDataSafe($_REQUEST);
        
        if($oResponse->_Validate()){
            if($oResponse->Save()){
                if(getRequest('photos')){
                    $this->Media_AttachMedia(getRequest('photos'), 'response', $oResponse->getId());
                }else{
                    $this->Media_RemoveTargetByTypeAndId($oResponse->getType(), $oResponse->getId());
                }
                
                $this->Viewer_AssignAjax('sUrlRedirect', getRequest('redirect'));
                
                $this->Message_AddNotice($this->Lang_Get('common.success.save'));
            }else{
                $this->Message_AddError($this->Lang_Get('common.error.error'));
            }
        }else{
            foreach ($oResponse->_getValidateErrors() as $aError) {
                $this->Message_AddError(array_shift($aError));
            }
        }
    }
    
    
    public function EventAjaxProposalCreate() {
        
        $oProposal = Engine::GetEntity('Talk_Proposal');
        $oProposal->_setDataSafe($_REQUEST);
        
        $oProposal->setState('publish');
        
        if($oProposal->_Validate()){
            if($oProposal->Save()){
                $this->Media_AttachMedia(getRequest('photos'), 'proposal', $oProposal->getId());
                
                $this->Viewer_AssignAjax('sUrlRedirect', getRequest('redirect'));
                
                $this->Message_AddNotice($this->Lang_Get('talk.proposal.notice.success_add'));
            }else{
                $this->Message_AddError($this->Lang_Get('common.error.error'));
            }
        }else{
            foreach ($oProposal->_getValidateErrors() as $aError) {
                $this->Message_AddError(array_shift($aError));
            }
        }
    }
    
    public function EventAjaxAnswerCreate() {
        
        $oAnswer = Engine::GetEntity('Talk_Message', ['type' => 'answer']);
        $oAnswer->_setDataSafe($_REQUEST);
        
        $oAnswer->setState('publish');
        
        
        if($oAnswer->_Validate()){
            if($oAnswer->Save()){
                $this->Media_AttachMedia(getRequest('photos'), 'answer', $oAnswer->getId());
                
                $this->Viewer_AssignAjax('sUrlRedirect', getRequest('redirect'));
                
                $this->Message_AddNotice($this->Lang_Get('talk.answer.notice.success_add'));
            }else{
                $this->Message_AddError($this->Lang_Get('common.error.error'));
            }
        }else{
            foreach ($oAnswer->_getValidateErrors() as $aError) {
                $this->Message_AddError(array_shift($aError));
            }
        }
    }
    
    public function EventAjaxArbitrageCreate() {
        
        $oArbitrage = Engine::GetEntity('Talk_Arbitrage');
        $oArbitrage->_setDataSafe($_REQUEST);
        
        $oArbitrage->setState('moderate');
        $oResponse = $oArbitrage->getResponse();
        $oResponse->setState('arbitrage');
        $oResponse->Save();
        
        if($this->User_GetUserCurrent()->getId() != $oResponse->getTargetId()){
            return $this->EventNotFound();
        }

        
        if($oArbitrage->_Validate()){
            if($oArbitrage->Save()){
                $this->Media_AttachMedia(getRequest('photos'), 'arbitrage', $oArbitrage->getId());
                
                $this->Viewer_AssignAjax('sUrlRedirect', $oArbitrage->getUrl());
                
                $this->Message_AddNotice($this->Lang_Get('talk.arbitrage.notice.success_add'));
            }else{
                $this->Message_AddError($this->Lang_Get('common.error.error'));
            }
        }else{
            foreach ($oArbitrage->_getValidateErrors() as $aError) {
                $this->Message_AddError(array_shift($aError));
            }
        }
    }
    
    
    public function EventAjaxArbitrageChat() {
        
        $oArbitrage = Engine::GetEntity('Talk_Arbitrage');
        $oArbitrage->_setDataSafe($_REQUEST);
        
        $oArbitrage->setState('chat');
        
        if($oArbitrage->_Validate()){
            if($oArbitrage->Save()){
                $this->Media_AttachMedia(getRequest('photos'), 'arbitrage', $oArbitrage->getId());
                
                $this->Viewer_AssignAjax('sUrlRedirect', getRequest('redirect'));
                
                $this->Message_AddNotice($this->Lang_Get('talk.arbitrage.notice.success_add'));
            }else{
                $this->Message_AddError($this->Lang_Get('common.error.error'));
            }
        }else{
            foreach ($oArbitrage->_getValidateErrors() as $aError) {
                $this->Message_AddError(array_shift($aError));
            }
        }
    }
    
    public function EventAjaxMessageDelete() {
        if(!$oMessage = $this->Talk_GetMessageById(getRequest('id'))){
            $this->Message_AddError($this->Lang_Get('common.error.error'));
            return;
        }
        
        if(!$oUserCurrent = $this->User_GetUserCurrent()){
            $this->Message_AddError($this->Lang_Get('common.error.error'));
            return;
        }
        
        if($oMessage->getUser()->getId() != $oUserCurrent->getId() and !$oUserCurrent->isAdministrator()){
            $this->Message_AddError($this->Lang_Get('common.error.error'));
            return;
        }
        
        if($oMessage->Delete()){
            $this->Message_AddNotice($this->Lang_Get('talk.answer.notice.success_remove'));
            $this->Viewer_AssignAjax('sUrlRedirect', getRequest('redirect'));
        }
        
    }   
    

}