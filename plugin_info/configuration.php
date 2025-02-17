<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');

if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}

include_file('core', 'LgParameters', 'class', 'lgthinq');

?>
<form class="form-horizontal" id="LgThinqForm">
    <fieldset>
	<legend>{{Authentification}} Step 1: Sélectionner la langue et le code pays</legend>
        <div class="form-group">
            <label class="col-lg-3 control-label">{{Country}}</label>
            <div class="col-lg-2">
                <input class="configKey form-control" data-l1key="LgCountry" id="LgCountry" placeholder="FR" />
            </div>
            <label class="col-lg-3 control-label">{{Language}}</label>
            <div class="col-lg-2">
                <input type="text" class="configKey form-control" data-l1key="LgLanguage" id="LgLanguage" list="LgLangList">
                        <datalist id="LgLangList">
                    <option>fr-FR</option>
                    <option>fr-CA</option>
                    <option>en-US</option>
                    <option>en-UK</option>
                </datalist>
            </div>
            <div class="col-lg-2">
                <a class="btn btn-success btn-xs" id="bt_AuthLgThinq"><i class="far fa-check-circle icon-white"></i> {{LG Account Login}}</a>
                <input type="hidden" class="configKey form-control" data-l1key="LgGateway" id="LgGateway" placeholder="url pour login LG" disabled />
            </div>
        </div>
  </fieldset>

    <fieldset>
        <div class="form-group">
            <label class="col-lg-3 control-label">{{Copiez / collez l'URL de redirection LG ici:}}</label>
            <div class="col-lg-7">
                <input class="configKey form-control" data-l1key="LgAuthUrl" id="LgAuthUrl" placeholder="url avec un token ..." />
            </div>
            <div class="col-lg-2">
                <a class="btn btn-success btn-xs" id="bt_RenewLgThinq"><i class="far fa-check-circle icon-white"></i> {{Renew Auth}}</a>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label">{{Ping du serveur plugin :}}</label>
            <div class="col-lg-2">
                <a class="btn btn-success btn-xs" id="bt_pingLgthinq"><i class="far fa-check-circle icon-white"></i> {{Ping ?}}</a>
            </div>
        <div class="form-group">
            <label class="col-lg-3 control-label">{{Download Configuration du Démon:}}</label>
            <div class="col-lg-2">
                <a class="btn btn-success btn-xs" id="lg_DownloadLgthinq"><i class="far fa-check-circle icon-white"></i> {{Download}}</a>
            </div>
        </div>
		
  </fieldset>
    <fieldset>
        <legend>{{Server Parameters}}</legend>
        <div class="form-group">
            <label class="col-lg-3 control-label">{{Port Server Local}}</label>
            <div class="col-lg-2">
                <input class="configKey form-control" data-l1key="PortServerLg" placeholder="5025" />
            </div>
            <label class="col-lg-3 control-label">{{URL Server Local}}</label>
            <div class="col-lg-2">
                <input class="configKey form-control" data-l1key="UrlServerLg" placeholder="http://127.0.0.1" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label">{{Python}}</label>
            <div class="col-lg-2">
                <input class="configKey form-control" data-l1key="PythonLg" />
            </div>
            <label class="col-lg-3 control-label">{{Wideq Lib version}}</label>
            <div class="col-lg-2">
                <select class="configKey form-control" data-l1key="WideqLibLg">
            <?php 
                foreach(LgParameters::getGithubBranches('https://api.github.com/repos/pifou25/wideq/branches') as $br){
                    printf("\t\t<option value=\"%s\">%s</option>\n", $br, $br);
                }
            ?>
                </select>
            </div>
        </div>
  </fieldset>
</form>

<div id='divAjaxAlert' style="display: none;"></div>

<script>
$( function(){
    $('#bt_AuthLgThinq').on('click',function(){
        // validate form
        var regPays = /^[A-Z]{2}$/;
        var regLang = /^[a-z]{2}-[A-Z]{2}$/;
        $('#LgCountry').val( $('#LgCountry').val().toUpperCase() );
        if(!regPays.test($('#LgCountry').val())){
            $('#divAjaxAlert').showAlert({message: 'Le Pays doit être 2 lettres! (' + $('#LgCountry').val() + ')', level: 'info'});
        }else if(!regLang.test( $('#LgLanguage').val() )){
            $('#divAjaxAlert').showAlert({message: 'La langue doit être au une combinaison de langue-pays (2 lettres minuscule)-(2 MAJUSCULES) (' + $('#LgLanguage').val() + ')', level: 'info'});
        }else{
            $('#divAjaxAlert').hide();
            /**
             * get URL gateway with lang and country code
             */
            $.ajax({
                type: 'POST',
                url: 'plugins/lgthinq/core/ajax/lgthinq.ajax.php',
                data: {
                    action: 'getGateway',
                    lang: $('#LgLanguage').val(),
                    country: $('#LgCountry').val()
                },
                dataType: 'json',
                global: false,
                error: function (request, status, error) {
                    handleAjaxError(request, status, error, $('#divAjaxAlert'));
                },
                success: function (data) {
                    if(data['state'] === 'ok'){
                        $('#LgGateway').val( data['result']['url']);
                        $('#bt_gateway').attr('href', data['result']['url']);
                        $('#LgAuthUrl').focus();
                        var win = window.open(data['result']['url'], '_blank');
                        if (win) {
                            //Browser has allowed it to be opened
                            win.focus();
                        } else {
                            //Browser has blocked it
                            $('#divAjaxAlert').showAlert({message: 'popup bloquée, cliquez sur le lien "Lg Account Login" pour vous identifier sur le Cloud LG, puis copiez l\'URL', level: 'info'});
                        }
                    }else{
                        $('#divAjaxAlert').showAlert({message: data['state'] + ' : ' + data['result'], level: 'danger'});
                    }
                }
            });
        }
    });

    $('#bt_pingLgthinq').on('click',function(){
        $('#divAjaxAlert').hide();
        $.post({
            url: 'plugins/lgthinq/core/ajax/lgthinq.ajax.php',
            data: {'action': 'ping'},
            dataType: 'json',
            global: false,
            error: function (request, status, error) {
                handleAjaxError(request, status, error, $('#divAjaxAlert'));
            },
            success: function (data) {
                if(data['state'] === 'ok'){
                    bootbox.alert(data['result']['message'] + ' ' + 
                            data['result']['starting']);
                }else{
                    $('#divAjaxAlert').showAlert({message: data['state'] + ' : ' + data['result'], level: 'danger'});
                }
            }
        });
    });

    $('#bt_RenewLgThinq').on('click',function(){
        $('#divAjaxAlert').hide();
        $.post({
            url: 'plugins/lgthinq/core/ajax/lgthinq.ajax.php',
            data: {'action': 'renew'},
            dataType: 'json',
            global: false,
            error: function (request, status, error) {
                handleAjaxError(request, status, error, $('#divAjaxAlert'));
            },
            success: function (data) {
                if(data['state'] === 'ok'){
                    bootbox.alert(data['result']['message']);
                }else{
                    $('#divAjaxAlert').showAlert({message: data['state'] + ' : ' + data['result'], level: 'danger'});
                }
            }
        });
    });

    $('#lg_DownloadLgthinq').click(function(e) {
        $('#divAjaxAlert').hide();
        e.preventDefault();  //stop the browser from following
        window.location.href = 'plugins/lgthinq/core/ajax/lgthinq.ajax.php?action=download';
    });

});
</script>
