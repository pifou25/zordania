<div class="debug block">
	<h1>Debug</h1>
	<dl>
		<if cond="false"><math oper="Template::printDebug()" /></if>

		<dt>_user</dt>
		<dd>
		<foreach cond="{_user} as {key} => {value}">
			[{key}] => 
			<if cond="is_array({value})">
                            <eval oper="$data .= print_r({value}, true)"/>
                        </if><else>{value}</else><br/>
		</foreach>
		</dd>
		<if cond="{_get}">
		<dt>_GET</dt>
		<dd><foreach cond="{_get} as {key} => {value}">
			[{key}] => 
			<if cond="is_array({value})">( array )</if><else>{value}</else><br/>
		</foreach></dd>
		</if>
		<if cond="{_post}">
		<dt>_POST</dt>
		<dd><foreach cond="{_post} as {key} => {value}">
			[{key}] => 
			<if cond="is_array({value})">( array )</if><else>{value}</else><br/>
		</foreach></dd>
		</if>
		<if cond="{_files}">
		<dt>_FILES</dt>
		<dd><foreach cond="{_files} as {key} => {value}">[{key}] => {value}<br/></foreach></dd>
		</if>
		<if cond="{_cookie}">
		<dt>_COOKIE</dt>
		<dd><foreach cond="{_cookie} as {key} => {value}">
			[{key}] => 
			<if cond="is_array({value})">( array )</if><else>{value}</else><br/>
		</foreach></dd>
		</if>

                <dt>SQL</dt>
		<dd>
			<ol>
                            <if cond="{eloQueries}">
                        <h3>ELOQUENT QUERIES</h3>
                                <foreach cond='{eloQueries} as {values}'>
			<li>
				<math oper="htmlspecialchars({values[query]})" /><br/>
				Bindings:  <math oper="print_r({values[bindings]}, true)" />
                                <br/>
				Temps: <math oper="round({values[time]},2)" /> ms
			</li>
                                </foreach>
                            </if>
                            
			</ol>
			Au total: <math oper='round({sv_total_sql_time},4)' /> sur <math oper='round(mtime()-{sv_diff},4)' /> secondes<br/>
			Donc: <math oper='round({sv_total_sql_time} /  (mtime()-{sv_diff}) * 100,2)' /> % du temps.
		</dd>
	</dl>
</div>
