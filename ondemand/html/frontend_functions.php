<?

function print_sidebar($sidebar_nav,$page="") {
        include('templates/sidebar.php');
}

function print_hero() {
	print '
          <div class="hero-unit">
            <p><a class="btn btn-primary btn-large">Learn more &raquo;</a></p>
          </div>

	';
}

function print_snippet() {
	print '
            <div class="span4">
              <h2>Heading</h2>
              <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
              <p><a class="btn" href="#">View details &raquo;</a></p>
            </div><!--/span-->

	';
}

function print_simple_table($data) {
	#print_r_pre($data);
	include('templates/simple_table.php');
}

function print_r_pre($data) {
	print "<pre>";
	print_r($data);
	print "</pre>";
}

function print_query($query) {
	define("PARSER_LIB_ROOT", "/usr/local/apache2/htdocs/lib/sqlparserlib/");
	require_once PARSER_LIB_ROOT."sqlparser.lib.php";
	print "<div class=query>";
	echo PMA_SQP_formatHtml(PMA_SQP_parse($query));
	print "</div>";
}

function prepare_json_string($obj) {
	$res = array();
	if(!empty($obj) && is_array($obj) && sizeof($obj) > 0) {
		$res[] = array_keys($obj[0]);
		foreach($obj as $entry) {
			$tmp = array();
			foreach($entry as $key=>$val) {
				$tmp[] = $val;
			}
			$res[] = $tmp;
		}
		$json_string = json_encode($res);
		$json_string = preg_replace('/"([-]?[0-9]+)"/','${1}',$json_string,-1);
		return $json_string;
	} else return NULL;
}
