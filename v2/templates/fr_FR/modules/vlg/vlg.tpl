
<div id="village">
	<img src="img/{_user[race]}/vlg/vlg.jpg" alt="village" />
	
	<foreach cond='{src_array} as {src_vars}'>
		<if cond="isset({src_conf[{src_vars[src_type]}][vlg]})">
			<zimgsrc race="{_user[race]}" type="{src_vars[src_type]}" class="btc" id="src_{src_vars[src_type]}" />
		</if>
	</foreach>

	<foreach cond='{btc_array} as {btc_vars}'>
		<a href="btc-use.html?btc_type={btc_vars[btc_type]}" class="zrdModPopUp" title="{btc[{_user[race]}][alt][{btc_vars[btc_type]}]}"><img src="img/{_user[race]}/vlg/{btc_vars[btc_type]}.png" class="btc" id="btc_{btc_vars[btc_type]}" /></a>
		
		
	</foreach>
</div>
<script type="text/javascript" src="js/vlg.js"></script>
<script type="text/javascript">
	VlgV2.init({_user[race]});
</script>
