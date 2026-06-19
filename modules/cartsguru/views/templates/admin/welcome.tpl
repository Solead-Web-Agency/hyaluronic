{*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @author    Carts Guru <prestashop@carts.guru>
 * @copyright Since 2017 Carts Guru
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 *}
<div id="cartsguru-welcome" class="{$activeView|escape:'htmlall':'UTF-8'}">
    <div class="header">
        <div class="inner-section">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 text-center">
                        <img class="logo" src="{$imagesUrl|escape:'urlpathinfo':'UTF-8'}logo_black.png" />
                    </div>
                </div>
                <div class="success-message text-center">
                    <img src="{$imagesUrl|escape:'urlpathinfo':'UTF-8'}success.png" />
                    <h1>{l s='You successfuly installed Carts Guru' mod='cartsguru'}</h1>
                </div>
                <div class="no-selected-store-message text-center">
                    <h1>{l s='The multistore option is enabled. If you want configure the module, please select store.' mod='cartsguru'}</h1>
                </div>
                <div class="row header-text">
                    <div class="col-lg-12">
                        <h1>{l s='The best way to recover your abandoned carts' mod='cartsguru'}</h1>
                        <h2>{l s='Carts Guru is the all-in-one solution to recover your abandoned carts and turn them into sales. Use Emails, SMS, Automatic Calls, Facebook & Instagram to convert more than 20% of your lost customers into sales.' mod='cartsguru'} </h2>
                    </div>
                </div>
                <form action="{$formUrl|escape:'htmlall':'UTF-8'}" method="post" class="have-account-form form">
                    <div class="form-title">{l s='Plug Carts Guru into your PrestaShop store' mod='cartsguru'}</div>
                    <button type="button" class="close-form-btn" onclick="cg_switchView()">
                        X
                    </button>
                    <div class="row">
                        <div class="col-md-4">
                            <input class="input full-width" name="siteid" placeholder="{l s='Site ID' mod='cartsguru'}" required value="{$siteid|escape:'htmlall':'UTF-8'}"/>
                        </div>
                        <div class="col-md-4">
                            <input class="input full-width" name="authkey" placeholder="{l s='Auth Key' mod='cartsguru'}" required value="{$authkey|escape:'htmlall':'UTF-8'}"/>
                        </div>
                        <div class="col-md-4">
                            <button class="btn-full full-width" name="submitConnect">{l s='Connect it now' mod='cartsguru'}</button>
                        </div>
                    </div>
                    <div class="sub-text row pull-right">
                        <div class="col-sm-12">
                            <span>{l s='Don t know this information?' mod='cartsguru'}</span>
                            <a class="link" target="_blank" href="https://app.carts.guru">{l s='Access it here' mod='cartsguru'}</a>
                        </div>
                    </div>
                </form>
                <form action="{$formUrl|escape:'htmlall':'UTF-8'}" method="post" class="reset-form">
                    <input type="hidden" name="reset-plugin" value="1">
                    <button id="resetForm" class="btn-text-only" name="submitConnect">{l s='Reset configuration for this scope' mod='cartsguru'}</button>
                </form>
            </div>
        </div>
    </div>
    <div class="triangle-slice"></div>
    <div class="footer">
        <div class="inner-section">
            <div class="container-fluid">
                 <div class="success-buttons">
                    <button class="btn-not-full" onclick="cg_switchView('view-have-account', 'view-success')">{l s='Edit my settings' mod='cartsguru'}</button>
                    <button class="btn-full" onclick="location.href='https://app.carts.guru/login?utm_source=appstore&utm_medium=prestashop'">{l s='Access to Carts Guru platform' mod='cartsguru'}</button>
                </div>
                <div class="try-buttons">
                    <div class="button-title">{l s='Enjoy all features during your 10-Days' mod='cartsguru'}</div>
                    <button class="btn-not-full" onclick="cg_switchView('view-have-account')">{l s='I have an account' mod='cartsguru'}</button>
                    <button class="btn-full" onclick="location.href='https://app.carts.guru/signup?utm_source=appstore&utm_medium=prestashop'" name="submitHasNoAccount">{l s='Try it now for free' mod='cartsguru'}</button>
                </div>
                <div class="row">
                    <div class="list">
                        <div class="col-lg-6">
                            <div class="list-item">
                                <img src="{$imagesUrl|escape:'urlpathinfo':'UTF-8'}checkmark.png" />
                                <span>{l s='Performance-based pricing.' mod='cartsguru'}</span>
                                (<a class="link" target="_blank" href="https://carts.guru?platform=prestashop&utm_source=appstore&utm_medium=prestashop">{l s='see details here' mod='cartsguru'}</a>)
                            </div>
                            <div class="list-item">
                                <img src="{$imagesUrl|escape:'urlpathinfo':'UTF-8'}checkmark.png" />
                                <span>{l s='Easy-to-use, incredibly intuitive solution' mod='cartsguru'}</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="list-item">
                                <img src="{$imagesUrl|escape:'urlpathinfo':'UTF-8'}checkmark.png" />
                                <span>{l s='Run unlimited campaigns, with unlimited actions' mod='cartsguru'}</span>
                            </div>
                            <div class="list-item">
                                <img src="{$imagesUrl|escape:'urlpathinfo':'UTF-8'}checkmark.png" />
                                <span>{l s='Risk Free. No credit cards required. Cancel any time' mod='cartsguru'}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 text-center text-bold features-title">{l s='Features' mod='cartsguru'}</div>
                </div>
                <div class="row">
                    <div clas="col-sm-12">
                        <div class="channel-block-list text-center">
                            <article class="channel-block">
                                <div class="channel-title">{l s='Email retargeting' mod='cartsguru'}</div>
                                <div class="channel-picture email"></div>
                                <div class="channel-link">{l s='Learn more' mod='cartsguru'}</div>
                                <div class="channel-info">
                                    <div class="channel-block-info-triangle"></div>
                                    <p class="no-margin">{l s='Customize campaigns with your brand. Launch them at the perfect moment with automation.' mod='cartsguru'}</p>
                                    <div class="col-xs-6 channel-ef-el">
                                        <div class="channel-ef">52%</div>
                                        <div class="channel-ef-t">{l s='Open rate' mod='cartsguru'}</div>
                                    </div>
                                    <div class="col-xs-6 channel-ef-el">
                                        <div class="channel-ef">14%</div>
                                        <div class="channel-ef-t">{l s='Click rate' mod='cartsguru'}</div>
                                    </div>
                                </div>
                            </article>
                            <article class="channel-block">
                                <div class="channel-title">{l s='SMS retargeting' mod='cartsguru'}</div>
                                <div class="channel-picture sms"></div>
                                <div class="channel-link">{l s='Learn more' mod='cartsguru'}</div>
                                <div class="channel-info">
                                    <div class="channel-block-info-triangle"></div>
                                    <p class="no-margin">{l s='With an outstanding 97% open rate. Add SMS Marketing to your mix.' mod='cartsguru'}</p>
                                    <div class="col-xs-6 channel-ef-el">
                                        <div class="channel-ef">20%</div>
                                        <div class="channel-ef-t">{l s='Conversion rate' mod='cartsguru'}</div>
                                    </div>
                                    <div class="col-xs-6 channel-ef-el">
                                        <div class="channel-ef">1%</div>
                                        <div class="channel-ef-t">{l s='Unsubscription' mod='cartsguru'}</div>
                                    </div>
                                </div>
                            </article>
                            <article class="channel-block">
                                <div class="channel-title">{l s='Facebook Messenger' mod='cartsguru'}</div>
                                <div class="channel-picture call"></div>
                                <div class="channel-link">{l s='Learn more' mod='cartsguru'}</div>
                                <div class="channel-info">
                                    <div class="channel-block-info-triangle"></div>
                                    <p class="no-margin">{l s='Save this for your most important products and customers. Contact your customer automatically.' mod='cartsguru'}</p>
                                    <div class="col-xs-6 channel-ef-el">
                                        <div class="channel-ef">45%</div>
                                        <div class="channel-ef-t">{l s='Click rate' mod='cartsguru'}</div>
                                    </div>
                                    <div class="col-xs-6 channel-ef-el">
                                        <div class="channel-ef">15%</div>
                                        <div class="channel-ef-t">{l s='Conversion rate' mod='cartsguru'}</div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 text-center">
                        <a class="text-bold features-title" target="_blank" href="https://carts.guru?platform=prestashop&utm_source=appstore&utm_medium=prestashop">
                            {l s='Access Carts Guru website' mod='cartsguru'}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
