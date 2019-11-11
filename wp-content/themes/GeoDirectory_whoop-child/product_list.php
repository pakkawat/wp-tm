<?php /* Template Name: Product list */ ?>

<?php
function get_products($pid, $PERPAGE_LIMIT) {
  global $wpdb;

  $sql = "SELECT * FROM products where post_id = ".$pid." ";
  $sql2 = "SELECT count(id) FROM products where post_id = ".$pid." ";
  //$sql = "SELECT * FROM wp_terms ";
  //$sql2 = "SELECT count(term_id) FROM wp_terms ";

  // getting parameters required for pagination
  $currentPage = 1;
  if(isset($_GET['pageNumber'])){
    $currentPage = $_GET['pageNumber'];
  }
  $startPage = ($currentPage-1)*$PERPAGE_LIMIT;
  if($startPage < 0) $startPage = 0;


  //adding limits to select query
  $sql .= " limit " . $startPage . "," . $PERPAGE_LIMIT;
  $result  = $wpdb->get_results( $sql );
  $count   = $wpdb->get_var( $sql2 );
  //file_put_contents( dirname(__FILE__).'/debug/debug_insert_images_.log', var_export( $count, true));
  return array($result, $count);
}

function pagination($count, $href, $PERPAGE_LIMIT) {
  $output = '';
  if(!isset($_REQUEST["pageNumber"])) $_REQUEST["pageNumber"] = 1;
  if($PERPAGE_LIMIT != 0)
    $pages  = ceil($count/$PERPAGE_LIMIT);

  $output .= '<ul class="pagination pagination-lg">';
  //if pages exists after loop's lower limit
  if($pages>1) {
    if(($_REQUEST["pageNumber"]-3)>0) {
      $output = $output . '<li class="page-item"><a href="' . $href . '&pageNumber=1">1</a></li>';
    }
    if(($_REQUEST["pageNumber"]-3)>1) {
      $output = $output . '<li class="page-item"><a href="' . $href . '&pageNumber='.($_REQUEST["pageNumber"]-1).'"><strong>&lt;</strong></a></li>';
    }

    //Loop for provides links for 2 pages before and after current page
    for($i=($_REQUEST["pageNumber"]-2); $i<=($_REQUEST["pageNumber"]+2); $i++)	{
      if($i<1) continue;
      if($i>$pages) break;
      if($_REQUEST["pageNumber"] == $i)
        $output = $output . '<li class="page-item active"><a href="#">'.$i.'</a></li>';
      else
        $output = $output . '<li class="page-item"><a href="' . $href . '&pageNumber='.$i .'">'.$i.'</a></li>';
    }

    //if pages exists after loop's upper limit
    if(($pages-($_REQUEST["pageNumber"]+2))>1) {
      $output = $output . '<li class="page-item"><a href="' . $href . '&pageNumber='.($_REQUEST["pageNumber"]+1).'"><strong>&gt;</strong></a></li>';
    }
    if(($pages-($_REQUEST["pageNumber"]+2))>0) {
      if($_REQUEST["pageNumber"] == $pages)
        $output = $output . '<li class="page-item active"><a href="#">' . ($pages) .'</a></li>';
      else
        $output = $output . '<li class="page-item"><a href="' . $href .'&pageNumber='.($pages) .'">' . ($pages) .'</a></li>';
    }

  }
  $output .= '</ul>';
  return $output;
}



global $current_user,$post, $wp_query;

$is_current_user_owner = false;
$PERPAGE_LIMIT = 5;
$href = "";
$count_product = 1;
$pid = (int)$_REQUEST['pid'];

$currentPage = 1;
if(isset($_GET['paged'])){
  $currentPage = $_GET['paged'];
}

if (isset($pid) && $pid != 0){
  $href = home_url('/product-list/') . '?pid='.$pid;
  $is_current_user_owner = geodir_listing_belong_to_current_user((int)$pid);
  if (is_user_logged_in() && $is_current_user_owner){
    //list($arrProducts, $count) = get_products($pid, $PERPAGE_LIMIT);

    $query_args = array(
      'posts_per_page' => 10,
      'is_geodir_loop' => true,
      'post_type' => 'gd_product',
      'pageno' => $currentPage,
      'order_by' => 'post_title'
    );

    add_filter('geodir_filter_widget_listings_where', 'tamzang_apply_shop_id', 10, 2);
    $product_listings = geodir_get_widget_listings($query_args);
    $count_product = geodir_get_widget_listings($query_args,true);

  }else{
    wp_redirect(get_permalink($pid));
  }
}else {
  wp_redirect(home_url());
}


?>

<?php get_header(); ?>

<div id="geodir_wrapper" class="geodir-single">
  <?php //geodir_breadcrumb();?>
  <div class="clearfix geodir-common">
    <div id="geodir_content" class="" role="main" style="width: 100%">
      <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class( 'cf' ); ?> role="article">
        <?php //echo '<div id="shopname" style="padding:1.5em;"><a href="'.get_permalink($pid).'">'.get_the_title($pid).'</a></div>'; 
              echo '<h2><li><a href="'.get_permalink( $pid ).'">'.get_the_title( $pid ).'</a></li></h2>';
        ?>
        <header class="article-header">
          <h1 class="page-title entry-title" itemprop="headline" style="padding-bottom:40px;">
            <div style="width:50%;float:left;">
              <?php the_title() ?>
            </div>
          </h1>
          <?php /*<p class="byline vcard"> <?php printf( __( 'Posted <time class="updated" datetime="%1$s" >%2$s</time> by <span class="author">%3$s</span>', GEODIRECTORY_FRAMEWORK ), get_the_time('c'), get_the_time(get_option('date_format')), get_the_author_link( get_the_author_meta( 'ID' ) )); ?> </p> */?>
        </header>
        <?php // end article header ?>
        <section class="entry-content cf" itemprop="articleBody">
          <?php the_content(); ?>


          <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                  <div class="modal-content">
                      <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                          <h4 class="modal-title" id="myModalLabel">ยืนยันการลบสินค้า</h4>
                      </div>
                      <div class="modal-body">
                          <p>คุณกำลังจะลบสินค้า <b><i class="title"></i></b> ออกจากร้าน</p>
                          <p>คุณต้องการดำเนินการต่อหรือไม่?</p>
                      </div>
                      <div class="modal-footer">
                          <button type="button" class="btn btn-default" data-dismiss="modal">ยกเลิก</button>
                          <button type="button" class="btn btn-danger btn-ok">ตกลง</button>
                      </div>
                  </div>
              </div>
          </div>

          <?php
            if ( wp_is_mobile() ){
          ?>


          <div class="table-responsive">
            <table id="product_table" class="table">
              <thead>
                <th></th>
              </thead>
              <tbody>
              <?php
                global $post;
                $current_post = $post;

                foreach ($product_listings as $product) {
                  $post = $product;
                  $GLOBALS['post'] = $post;
                  setup_postdata($post);
                  echo '<tr id="'.$post->ID.'">';
                  echo '<td>';
                  echo 'ชื่อ: '.$post->post_title.'<br>';
                  echo 'ราคา: '.$post->geodir_price.'<br>';
                  echo 'รายละเอียดแบบย่อ	: '.wp_trim_excerpt().'<br>';
                  $date = date_create($post->post_modified);
                  echo 'แก้ไขล่าสุดเมื่อ: '.date_format($date, 'd-m-Y H:i:s').'<br>';
                  echo '<div class="order-row">';
                  echo '<div class="order-col-6"><a class="btn btn-primary btn-block" href="'. home_url('/add-listing/') . '?pid='.$post->ID .'"><span style="color: #ffffff !important;" >แก้ไข</span></a></div>';
                  echo '<div class="order-col-6"><a class="btn btn-danger btn-block" href="#" data-record-id="'.$post->ID.'" data-record-title="'.$post->post_title.'" data-record-nonce="'.wp_create_nonce( 'delete_product_' . $post->ID ).'" data-toggle="modal" data-target="#confirm-delete" ><span style="color: #ffffff !important;" >ลบ</span></a></div>';
                  echo '</div>';
                  echo '</td>';
                  echo '</tr>';
                }

                $GLOBALS['post'] = $current_post;
                if (!empty($current_post)) {
                    setup_postdata($current_post);
                }
                //wp_reset_query();
    		      ?>
              </tbody>
            </table>

            <?php product_pagination(ceil($count_product / 10),$currentPage,'«','»'); ?>

          </div>





          <?php
            }else{
          ?>

          <div class="table-responsive">
            <table id="product_table" class="table">
              <thead>
                <th>ชื่อ</th>
                <th>ราคา</th>
                <th>รายละเอียดแบบย่อ</th>
                <!--<th>จำนวน</th>-->
                <!--<th>ไม่จำกัดจำนวน</th>-->
                <th>แก้ไขล่าสุดเมื่อ</th>
                <th>แก้ไข</th>
                <th>ลบ</th>
              </thead>
              <tbody>
              <?php
                global $post;
                $current_post = $post;

                foreach ($product_listings as $product) {
                  $post = $product;
                  $GLOBALS['post'] = $post;
                  setup_postdata($post);
                  echo '<tr id="'.$post->ID.'">';
                  echo '<td style="text-align:center;">'.$post->post_title.'</td>';
                  echo '<td style="text-align:center;">'.$post->geodir_price.'</td>';
                  echo '<td>'.wp_trim_excerpt().'</td>';
                  //echo '<td style="text-align:center;">'.$post->geodir_stock.'</td>';
                  //echo '<td style="text-align:center;">'.($post->geodir_unlimited == '1' ? 'ใช่' : 'ไม่').'</td>';
                  $date = date_create($post->post_modified);
                  echo '<td>'.date_format($date, 'd-m-Y H:i:s').'</td>';
                  echo '<td style="text-align:center;"><a class="btn btn-primary btn-block" href="'. home_url('/add-listing/') . '?pid='.$post->ID .'"><span style="color: #ffffff !important;" >แก้ไข</span></a></td>';
                  echo '<td style="text-align:center;"><a class="btn btn-danger btn-block" href="#" data-record-id="'.$post->ID.'" data-record-title="'.$post->post_title.'" data-record-nonce="'.wp_create_nonce( 'delete_product_' . $post->ID ).'" data-toggle="modal" data-target="#confirm-delete" ><span style="color: #ffffff !important;" >ลบ</span></a></td>';
                  echo '</tr>';
                }

                $GLOBALS['post'] = $current_post;
                if (!empty($current_post)) {
                    setup_postdata($current_post);
                }
                //wp_reset_query();
    		      ?>
              </tbody>
            </table>

            <?php product_pagination(ceil($count_product / 10),$currentPage,'«','»'); ?>
            <?php 
            //echo 'brfore_geodir_pagination:'.print_r($wp_query).'<br><br><br>';
            //geodir_pagination('', '', '--<<', '>>--', 3, true);

            ?>
          </div>
          <?php }// else wp_is_mobile ?>

        </section>
        <?php // end article section ?>



        <footer class="article-footer cf"><?php echo pagination($count, $href, $PERPAGE_LIMIT); ?></footer>
      </article>
      <?php endwhile; else : ?>
      <article id="post-not-found" class="hentry cf">
        <header class="article-header">
          <h1>
            <?php _e( 'Oops, Post Not Found!', GEODIRECTORY_FRAMEWORK ); ?>
          </h1>
        </header>
        <section class="entry-content">
          <p>
            <?php _e( 'Uh Oh. Something is missing. Try double checking things.', GEODIRECTORY_FRAMEWORK ); ?>
          </p>
        </section>
        <footer class="article-footer">
          <p>
            <?php _e( 'This is the error message in the page.php template.', GEODIRECTORY_FRAMEWORK ); ?>
          </p>
        </footer>
      </article>
      <?php endif; ?>
    </div>

  </div>
</div>
<?php get_footer(); ?>
