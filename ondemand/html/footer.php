
      <!--
      <footer>
        <p>&copy; Flurry</p>
      </footer>
      -->

    </div><!--/.fluid-container-->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="/static/js/jquery-1.7.2.js"></script>
    <script src="/static/js/bootstrap-transition.js"></script>
    <script src="/static/js/bootstrap-alert.js"></script>
    <script src="/static/js/bootstrap-modal.js"></script>
    <script src="/static/js/bootstrap-dropdown.js"></script>
    <script src="/static/js/bootstrap-scrollspy.js"></script>
    <script src="/static/js/bootstrap-tab.js"></script>
    <script src="/static/js/bootstrap-tooltip.js"></script>
    <script src="/static/js/bootstrap-popover.js"></script>
    <script src="/static/js/bootstrap-button.js"></script>
    <script src="/static/js/bootstrap-collapse.js"></script>
    <script src="/static/js/bootstrap-carousel.js"></script>
    <script src="/static/js/bootstrap-typeahead.js"></script>
    <script src="/static/js/jquery.jeditable.js"></script>

    <? if($page == "/signup.php") { ?>
    <script src="/static/js/jquery.validate.js"></script>
    <script src="/static/js/registration.js"></script>
    <? } ?>

    <? if($page == "/pages.php") { ?>
    <script src="/static/js/jquery.dataTables.js"></script>
    <script src="/static/js/get_column_data.js"></script>
    <script type="text/javascript">
      $(document).ready(function() {
        var cb = $('#query_table').dataTable({
		"sPaginationType": "full_numbers",
		"fnDrawCallback": function () {
			//console.debug( 'Start: '+ this.fnPagingInfo().iStart + ' End: ' + this.fnPagingInfo().iEnd);
			var start = this.fnPagingInfo().iStart;
			var end = this.fnPagingInfo().iEnd;
			var oSettings = this.fnSettings();
			var headers = oSettings.aoHeader;
			var table_width = headers[0].length
			var headers_out = new Array(table_width)
			var column_data = new Array(table_width);
			var j=0;
			for(i=0;i<table_width;i++) {
				headers_out[j] = headers[0][i].cell.innerHTML;
				column_data[j] = this.fnGetColumnData(i,false,true,false);
				j++;
			}
			var out_array = new Array(end-start);
			out_array[0] = headers_out;
			var j=1;
			for(i=start;i<end;i++) {
				var row = new Array(table_width);
				for(k=0;k<table_width;k++) {
					row[k] = column_data[k][i];
				}
				out_array[j] = row;
				j++;
			}
			//console.debug(<?=$json_data?>);
			if(cb != null) {
				refreshChart(out_array);
			}
		}
        });
      });
    </script>
    <? } ?>

  </body>
</html>
