La compétence <bbimgcomp type="{cpt[cpid]}" race="{cpt[race]}" /> du héros décime les rangs ennemis :
<foreach cond="{cpt[res]} as {cpt1}">
	<foreach cond="{cpt1[unt]} as {type} => {nb}">
		{nb} <bbimgunt type="{type}" race="{cpt1[race]}" />
	</foreach>
</foreach>
