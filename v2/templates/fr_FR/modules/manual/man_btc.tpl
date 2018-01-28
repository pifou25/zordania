<dl>
 <foreach cond='{man_array} as {btc_id} => {btc_value}'>
      <dt id="btc_{btc_id}">
      <h3>
{btc[{man_race}][alt][{btc_id}]}
      </h3>
      </dt>
      <dd>
      <p>
	     <# img style="align: left" src="img/{man_race}/btc/{btc_id}.png" alt="{btc[{man_race}][alt][{btc_id}]}" title="{btc[{man_race}][alt][{btc_id}]}" / #>
		 <zimgbtc type="{btc_id}" race="{man_race}"  style="float: left;" />
		 {btc[{man_race}][descr][{btc_id}]}  
	  
     <if cond='isset({btc_value[bonus]})'>
          Bonus: 
          <if cond='isset({btc_value[bonus][gen]})'>{btc_value[bonus][gen]} <img src="img/{man_race}/div/atq.png" alt="Valeur d'attaque" /></if>
          <if cond='isset({btc_value[bonus][bon]})'>{btc_value[bonus][bon]} % <img src="img/{man_race}/div/def.png" alt="Bonus défense" /></if>
          <br />
     </if>
      Solidité: {btc_value[vie]} <br/>
      Temps : {btc_value[tours]} Tour(s)/Travailleur<br/>
      <if cond='isset({btc_value[prod_pop]})'>
		Place : {btc_value[prod_pop]} <img src="img/{man_race}/{man_race}.png" alt="Place" title="Place" /><br/>
      </if>

     <if cond='isset({btc_value[prix_res]})'>
          Prix :
          <foreach cond='{btc_value[prix_res]} as {res_type} => {res_nb}'>
               {res_nb} <zimgres type="{res_type}" race="{man_race}" />
          </foreach></br>
     </if>
     <if cond='isset({btc_value[prix_trn]})'>
          Terrains: 
          <foreach cond='{btc_value[prix_trn]} as {trn_type} => {trn_nb}'>
               {trn_nb} <zimgtrn type="{trn_type}" race="{man_race}" />
          </foreach><br/>
     </if>
     <if cond='isset({btc_value[prix_unt]})'>
          Unités Nécessaires :
          <foreach cond='{btc_value[prix_unt]} as {unt_type} => {unt_nb}'>
               {unt_nb} <zimgunt type="{unt_type}" race="{man_race}" />
          </foreach><br/>
     </if>
     <if cond='isset({btc_value[need_src]})'>
            Recherche : 
            <foreach cond='{btc_value[need_src]} as {src_type}'>
                 <zimgsrc type="{src_type}" race="{man_race}" />
            </foreach><br/>
       </if>
     <if cond="isset({btc_value[need_btc]})">
          Bâtiment:
          <foreach cond="{btc_value[need_btc]} as {btc_id2}">
			   <span class="menu_module"><a href="#btc_{btc_id2}">{btc[{man_race}][alt][{btc_id2}]}</a></span>
          </foreach><br/>
     </if>

     <if cond='isset({btc_value[prod_res_auto]})'>
          Produit :
          <foreach cond='{btc_value[prod_res_auto]} as {res_type} => {res_nb}'>
               {res_nb} <zimgres type="{res_type}" race="{man_race}" />
          </foreach><br/>
     </if>
     
       <if cond="isset({btc_value[limite]})">
            Maximum: {btc_value[limite]}<br/>
       </if>
	   <div class="cleaner"></div>
      </dd>
 </foreach>
 </dl>
