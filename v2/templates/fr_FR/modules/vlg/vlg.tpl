<div id="village">
	<img src="img/{_user[race]}/vlg/vlg.jpg" alt="village" />
	
	<foreach cond='{src_array} as {src_vars}'>
		<if cond="isset({src_conf[{src_vars[src_type]}][vlg]})">
			<zimgsrc race="{_user[race]}" type="{src_vars[src_type]}" class="btc" id="src_{src_vars[src_type]}" />
		</if>
	</foreach>
	
	<foreach cond='{btc_array} as {btc_vars}'>
	
		<if cond="isset({btc_conf[{btc_vars[btc_type]}][prod_unt]})">
			<a href="btc-use.html?btc_type={btc_vars[btc_type]}&amp;sub=unt" class="zrdModPopUp" title="{btc[{_user[race]}][alt][{btc_vars[btc_type]}]}">
			<zimgbtc race="{_user[race]}" type="{btc_vars[btc_type]}" class="btc" id="btc_{btc_vars[btc_type]}" /></a>
		</if>
		<elseif cond="isset({btc_conf[{btc_vars[btc_type]}][prod_res]})">
			<a href="btc-use.html?btc_type={btc_vars[btc_type]}&amp;sub=res" class="zrdModPopUp" title="{btc[{_user[race]}][alt][{btc_vars[btc_type]}]}">
			<zimgbtc race="{_user[race]}" type="{btc_vars[btc_type]}" class="btc" id="btc_{btc_vars[btc_type]}" /></a>
		</elseif>
		<elseif cond="isset({btc_conf[{btc_vars[btc_type]}][prod_src]})">
			<a href="btc-use.html?btc_type={btc_vars[btc_type]}&amp;sub=src" class="zrdModPopUp" title="{btc[{_user[race]}][alt][{btc_vars[btc_type]}]}">
			<zimgbtc race="{_user[race]}" type="{btc_vars[btc_type]}" class="btc" id="btc_{btc_vars[btc_type]}" /></a>
		</elseif>
		<else>
			<a href="btc-use.html?btc_type={btc_vars[btc_type]}" class="zrdModPopUp" title="{btc[{_user[race]}][alt][{btc_vars[btc_type]}]}">
			<zimgbtc race="{_user[race]}" type="{btc_vars[btc_type]}" class="btc" id="btc_{btc_vars[btc_type]}" /></a>
		</else>
	</foreach>
	
	<script type="text/javascript" src="js/vlg.js"></script>
	<script type="text/javascript">
		VlgV2.init({_user[race]});
	</script>
</div>
