{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if $this_error != ''}
<p><code>{$this_error}</code></p>
{/if}

{if "creditcard"|in_array:$poTypes}
<p class="payment_module">
	<a href="{$link->getModuleLink('epgpaymentpage', 'payment', ['ptype' => 'Creditcard', 'ttype' => 'Sale' , 'Token' => '0'])|escape:'html'}" title="{l s='Pay with Creditcard' mod='epgpaymentpage'}">
		<img src="{$this_path_epg}img/creditcard_logo.gif" alt="{l s='Pay with Credit Card' mod='epgpaymentpage'}" width="150" height="46"/>
		{l s='Pay with CreditCard' mod='epgpaymentpage'}
	</a>
</p>
{/if}
{if "dd"|in_array:$poTypes}
<p class="payment_module">
	<a href="{$link->getModuleLink('epgpaymentpage', 'payment', ['ptype' => 'Directdebit', 'ttype' => 'Sale' , 'Token' => '0'])|escape:'html'}" title="{l s='Pay with Directdebit' mod='epgpaymentpage'}">
		<img src="{$this_path_epg}img/sepa_logo.png" alt="{l s='Pay with Direct Debit' mod='epgpaymentpage'}" width="150" height="84"/>
		{l s='Pay with Direct Debit' mod='epgpaymentpage'}&nbsp;<span>{l s='(order processing will be longer)' mod='epgpaymentpage'}</span>
	</a>
</p>
{/if}
{if "sofort"|in_array:$poTypes}
<p class="payment_module">
	<a href="{$link->getModuleLink('epgpaymentpage', 'payment', ['ptype' => 'Sofort', 'ttype' => 'Sale' , 'Token' => '0'])|escape:'html'}" title="{l s='Pay with Sofort Banking' mod='epgpaymentpage'}">
		<img src="{$this_path_epg}img/sofort_logo.png" alt="{l s='Pay with Sofort Banking' mod='epgpaymentpage'}" width="150" height="84"/>
		{l s='Pay with Sofort Banking' mod='epgpaymentpage'}&nbsp;<span>{l s='(order processing will be longer)' mod='epgpaymentpage'}</span>
	</a>
</p>
{/if}
{if "astropay"|in_array:$poTypes}
<p class="payment_module">
	<a href="{$link->getModuleLink('epgpaymentpage', 'payment', ['ptype' => 'Astropay', 'ttype' => 'Sale' , 'Token' => '0'])|escape:'html'}" title="{l s='Pay with Astropay' mod='epgpaymentpage'}">
		<img src="{$this_path_epg}img/astropay_logo.png" alt="{l s='Pay with Astropay' mod='epgpaymentpage'}" width="150" height="84"/>
		{l s='Pay with Astropay' mod='epgpaymentpage'}
	</a>
</p>
{/if}
{if "paysafecard"|in_array:$poTypes}
<p class="payment_module">
	<a href="{$link->getModuleLink('epgpaymentpage', 'payment', ['ptype' => 'Paysafecard', 'ttype' => 'Sale' , 'Token' => '0'])|escape:'html'}" title="{l s='Pay with Paysafecard' mod='epgpaymentpage'}">
		<img src="{$this_path_epg}img/psc_logo.png" alt="{l s='Pay with Paysafecard' mod='epgpaymentpage'}" width="150" height="84"/>
		{l s='Pay with Paysafecard' mod='epgpaymentpage'}
	</a>
</p>
{/if}
{if "neteller"|in_array:$poTypes}
<p class="payment_module">
	<a href="{$link->getModuleLink('epgpaymentpage', 'payment', ['ptype' => 'Neteller', 'ttype' => 'Sale' , 'Token' => '0'])|escape:'html'}" title="{l s='Pay with Neteller' mod='epgpaymentpage'}">
		<img src="{$this_path_epg}img/neteller_logo.png" alt="{l s='Pay with Neteller' mod='epgpaymentpage'}" width="150" height="84"/>
		{l s='Pay with Neteller' mod='epgpaymentpage'}
	</a>
</p>
{/if}
{if "giropay"|in_array:$poTypes}
<p class="payment_module">
	<a href="{$link->getModuleLink('epgpaymentpage', 'payment', ['ptype' => 'Giropay', 'ttype' => 'Sale' , 'Token' => '0'])|escape:'html'}" title="{l s='Pay with Giropay' mod='epgpaymentpage'}">
		<img src="{$this_path_epg}img/giropay_logo.png" alt="{l s='Pay with Giropay' mod='epgpaymentpage'}" width="150" height="84"/>
		{l s='Pay with Giropay' mod='epgpaymentpage'}
	</a>
</p>
{/if}
{if "paypal"|in_array:$poTypes}
<p class="payment_module">
	<a href="{$link->getModuleLink('epgpaymentpage', 'payment', ['ptype' => 'Paypal', 'ttype' => 'Sale' , 'Token' => '0'])|escape:'html'}" title="{l s='Pay with Paypal' mod='epgpaymentpage'}">
		<img src="{$this_path_epg}img/paypal_logo.png" alt="{l s='Pay with Paybal' mod='epgpaymentpage'}" width="150" height="84"/>
		{l s='Pay with Paypal' mod='epgpaymentpage'}&nbsp;<span>{l s='(order processing might take longer)' mod='epgpaymentpage'}</span>
	</a>
</p>
{/if}
{if "skrill"|in_array:$poTypes}
<p class="payment_module">
	<a href="{$link->getModuleLink('epgpaymentpage', 'payment', ['ptype' => 'Skrill', 'ttype' => 'Sale' , 'Token' => '0'])|escape:'html'}" title="{l s='Pay with Skrill' mod='epgpaymentpage'}">
		<img src="{$this_path_epg}img/skrill_logo.png" alt="{l s='Pay with Skrill' mod='epgpaymentpage'}" width="150" height="84"/>
		{l s='Pay with Skrill' mod='epgpaymentpage'}&nbsp;<span>{l s='(order processing might take longer)' mod='epgpaymentpage'}</span>
	</a>
</p>
{/if}
{if "ideal"|in_array:$poTypes}
<p class="payment_module">
        <a href="{$link->getModuleLink('epgpaymentpage', 'payment', ['ptype' => 'Ideal', 'ttype' => 'Sale' , 'Token' => '0'])|escape:'html'}" title="{l s='Pay with iDeal' mod='epgpaymentpage'}">
                <img src="{$this_path_epg}img/ideal_logo.png" alt="{l s='Pay with iDeal' mod='epgpaymentpage'}" width="150" height="84"/>
                {l s='Pay with iDeal' mod='epgpaymentpage'}
        </a>
</p>
{/if}
{if "eps"|in_array:$poTypes}
<p class="payment_module">
        <a href="{$link->getModuleLink('epgpaymentpage', 'payment', ['ptype' => 'EPS', 'ttype' => 'Sale' , 'Token' => '0'])|escape:'html'}" title="{l s='Pay with EPS' mod='epgpaymentpage'}">
                <img src="{$this_path_epg}img/eps_logo.png" alt="{l s='Pay with iDeal' mod='epgpaymentpage'}" width="150" height="84"/>
                {l s='Pay with EPS' mod='epgpaymentpage'}
        </a>
</p>
{/if}
