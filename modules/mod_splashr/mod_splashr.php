<?php
/**
 * @version SVN: $Id$
 * @package    SplashR
 * @subpackage Base
 * @author     Michael Richey {@link http://www.richeyweb.com}
 * @author     Created on 01-Mar-2010
 */

//-- No direct access
defined('_JEXEC') or die('=;)');
$doc = JFactory::getDocument();
$displayfrequency = ((int)$params->get('frequencytype',0) == 0)? 'sessiononly' : $params->get('frequency',0).' days';
$skipbutton = ((int)$params->get('disableskip',0) == 1 && (int)$params->get('autohide',0) > 0)? '' : '<a style="position:absolute; top: 2px; right: 5px" href="javascript:splashpage.closeit()" title="Skip to Content"><img src="'.JURI::root(true).'/media/mod_splashr/assets/images/skip.gif" border="0" width="114px" height="23px" /></a>';
$script='/***********************************************
* Splash Page script- (c) Dynamic Drive DHTML code library (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
* Adapted for use with Joomla! by Michael Richey - http://www.richeyweb.com
***********************************************/
var splashpage={
splashenabled: 1,
splashpageurl: "'.$params->get('splashurl').'",
enablefrequency: '.$params->get('enablefrequency').',
displayfrequency: "'.$displayfrequency.'",
defineheader: \'<div style="padding: 5px; color: white; font: bold 16px Verdana; background: black url('.JURI::root(true).'/media/mod_splashr/assets/images/blockdefault.gif) center center repeat-x;">'.$skipbutton.$params->get('splashtitle').'...</div>\',
cookiename: ["splashpagecookie", "path=/"],
autohidetimer: '.$params->get('autohide').',
launch:false,
browserdetectstr: (window.opera&&window.getSelection) || (!window.opera && window.XMLHttpRequest), //current browser detect string to limit the script to be run in (Opera9 and other "modern" browsers)

output:function(){
        document.write(\'<div id="slashpage" style="position: absolute; z-index: 10000; color: white; background-color:white">\') //Main splashpage container
        document.write(this.defineheader) //header portion of splashpage
        document.write(\'<iframe name="splashpage-iframe" src="about:blank" style="margin:0; padding:0; width:100%; height: 100%"></iframe>\') //iframe
        document.write(\'<br />&nbsp;</div>\')
        this.splashpageref=document.getElementById("slashpage")
        this.splashiframeref=window.frames["splashpage-iframe"]
        this.splashiframeref.location.replace(this.splashpageurl) //Load desired URL into splashpage iframe
        this.standardbody=(document.compatMode=="CSS1Compat")? document.documentElement : document.body
        if (!/safari/i.test(navigator.userAgent)) //if not Safari, disable document scrollbars
        this.standardbody.style.overflow="hidden"
        this.splashpageref.style.left=0
        this.splashpageref.style.top=0
        this.splashpageref.style.width="100%"
        this.splashpageref.style.height="100%"
        this.moveuptimer=setInterval("window.scrollTo(0,0)", 50)
},

closeit:function(){
        clearInterval(this.moveuptimer)
        this.splashpageref.style.display="none"
        this.splashiframeref.location.replace("about:blank")
        this.standardbody.style.overflow="auto"
},

init:function(){
        if (this.enablefrequency==1){ //if frequency control turned on
                if (/sessiononly/i.test(this.displayfrequency)){ //if session only control
                        if (this.getCookie(this.cookiename[0]+"_s")==null){ //if session cookie is empty
                                this.setCookie(this.cookiename[0]+"_s", "loaded")
                                this.launch=true
                        }
                }
                else if (/day/i.test(this.displayfrequency)){ //if persistence control in days
                        if (this.getCookie(this.cookiename[0])==null || parseInt(this.getCookie(this.cookiename[0]))!=parseInt(this.displayfrequency)){ //if persistent cookie is empty or admin has changed number of days to persist from that of the stored value (meaning, reset it)
                                this.setCookie(this.cookiename[0], parseInt(this.displayfrequency), parseInt(this.displayfrequency))
                                this.launch=true
                        }
                }
        }
        else //else if enablefrequency is off
                this.launch=true
        if (this.launch){
                this.output()
                if (parseInt(this.autohidetimer)>0)
                        setTimeout("splashpage.closeit()", parseInt(this.autohidetimer)*1000)
        }
},

getCookie:function(Name){
        var re=new RegExp(Name+"=[^;]+", "i"); //construct RE to search for target name/value pair
        if (document.cookie.match(re)) //if cookie found
                return document.cookie.match(re)[0].split("=")[1] //return its value
        return null
},

setCookie:function(name, value, days){
        var expireDate = new Date()
        //set "expstring" to either an explicit date (past or future)
        if (typeof days!="undefined"){ //if set persistent cookie
                var expstring=expireDate.setDate(expireDate.getDate()+parseInt(days))
                document.cookie = name+"="+value+"; expires="+expireDate.toGMTString()+"; "+splashpage.cookiename[1] //last portion sets cookie path
        }
else //else if this is a session only cookie setting
        document.cookie = name+"="+value+"; "+splashpage.cookiename[1] //last portion sets cookie path
}

}

if (splashpage.browserdetectstr && splashpage.splashenabled==1) splashpage.init()';

if($params->get('modaltype',0) == 1) {
        JHTML::_('behavior.modal');
        $size=explode('x',$params->get('squeezeboxsize','600x600'));
        $return = array();
        $return[]='if (typeof jQuery != \'undefined\') jQuery.noConflict();';
        $return[]='var splashr'.$module->id.'freq = "'.$displayfrequency.'";';
        $return[]='var splashr'.$module->id.'display = true;';
        $return[]='var splashr'.$module->id.'CookieValue = Cookie.read(\'splashr'.$module->id.'\');';
        $return[]='window.addEvent(\'domready\',function(){';
        if((int)$params->get('enablefrequency') == 1) {
            $return[]='if(splashr'.$module->id.'freq == "sessiononly") {';
            $return[]="\t".'if(splashr'.$module->id.'CookieValue == null) {';
            $return[]="\t\t".'var splashr'.$module->id.'Cookie = Cookie.write(\'splashr'.$module->id.'\', \'sessiononly\');';
            $return[]="\t".'} else {';
            $return[]="\t\t".'splashr'.$module->id.'display = false;';
            $return[]="\t".'}';
            $return[]='} else {';
            $return[]="\t".'if(splashr'.$module->id.'CookieValue == null) {';
            $return[]="\t\t".'var splashr'.$module->id.'Cookie = Cookie.write(\'splashr'.$module->id.'\', \'days\',{\'duration\':'.$params->get('frequency').'});';
            $return[]="\t".'} else {';
            $return[]="\t\t".'splashr'.$module->id.'display = false;';
            $return[]="\t".'}';
            $return[]='}';
        }
        $return[]="\tif (splashr".$module->id."display == true) {";
        $return[]="\t\tSqueezeBox.open('".$params->get('splashurl')."', {'handler': 'iframe','size':{'x':".$size[0].",'y':".$size[1]."}});";
        if((int)$params->get('autohide') > 0) {
            $return[]="\t\t(function(){document.id('sbox-btn-close').fireEvent('click')}).delay(".((int)$params->get('autohide')*1000).");";
        }
        $return[]="\t}";
        $return[]='});';
        $script = implode("\n",$return);
}

if (JRequest::getVar('tmpl') != 'component') $doc->addScriptDeclaration($script);