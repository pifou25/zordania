<div id="village">

<if cond="isset({sv_site_debug})">
<style>
#village .btc{
    border: 1px solid #FFF;
}
#village .btc:hover{
    border: 1px solid #F00;
}
</style>
</if>

	<img src="img/{_user[race]}/vlg/{imgvlg}" alt="village" id="fondvlg" />
	
	<foreach cond='{src_array} as {src_vars}'>
		<if cond="isset({src_conf[{src_vars[src_type]}][vlg]})">
			<zimgsrc race="{_user[race]}" type="{src_vars[src_type]}" class="btc" id="src_{src_vars[src_type]}" />
		</if>
	</foreach>
	
	<foreach cond='{btc_array} as {btc_vars}'>
	
		<if cond="{forteresse} === false || {btc_vars[btc_type]} != {forteresse}">
		<if cond="isset({btc_conf[{btc_vars[btc_type]}][prod_unt]})">
			<a href="btc-use.html?btc_type={btc_vars[btc_type]}&amp;sub=unt" class="zrdPopUp" title="{btc[{_user[race]}][alt][{btc_vars[btc_type]}]}">
			<zimgbtc race="{_user[race]}" type="{btc_vars[btc_type]}" class="btc" id="btc_{btc_vars[btc_type]}" /></a>
		</if>
		<elseif cond="isset({btc_conf[{btc_vars[btc_type]}][prod_res]})">
			<a href="btc-use.html?btc_type={btc_vars[btc_type]}&amp;sub=res" class="zrdPopUp" title="{btc[{_user[race]}][alt][{btc_vars[btc_type]}]}">
			<zimgbtc race="{_user[race]}" type="{btc_vars[btc_type]}" class="btc" id="btc_{btc_vars[btc_type]}" /></a>
		</elseif>
		<elseif cond="isset({btc_conf[{btc_vars[btc_type]}][prod_src]})">
			<a href="btc-use.html?btc_type={btc_vars[btc_type]}&amp;sub=src" class="zrdPopUp" title="{btc[{_user[race]}][alt][{btc_vars[btc_type]}]}">
			<zimgbtc race="{_user[race]}" type="{btc_vars[btc_type]}" class="btc" id="btc_{btc_vars[btc_type]}" /></a>
		</elseif>
		<else>
			<a href="btc-use.html?btc_type={btc_vars[btc_type]}" class="zrdPopUp" title="{btc[{_user[race]}][alt][{btc_vars[btc_type]}]}">
			<zimgbtc race="{_user[race]}" type="{btc_vars[btc_type]}" class="btc" id="btc_{btc_vars[btc_type]}" /></a>
		</else>
		</if>
	</foreach>
	
	<script type="text/javascript" src="js/vlg.js"></script>
	<script type="text/javascript">
		$(function(){
			// mobile or desktop design
			var isMobile  = isVisible('#bp_mobile');
			var forteresse = <if cond="{forteresse}">1</if><else>0</else>;
			VlgV2.init({_user[race]}, isMobile, forteresse);
		});
	</script>
</div>
