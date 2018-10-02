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



global $current_user;

$is_current_user_owner = false;
$PERPAGE_LIMIT = 5;
$href = "";
$pid = (int)$_REQUEST['pid'];

if (isset($pid) && $pid != 0){
  $href = home_url('/product-list/') . '?pid='.$pid;
  $is_current_user_owner = geodir_listing_belong_to_current_user((int)$pid);
  if (is_user_logged_in() && $is_current_user_owner){
    list($arrProducts, $count) = get_products($pid, $PERPAGE_LIMIT);

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
        <header class="article-header">
          <h1 class="page-title entry-title" itemprop="headline" style="padding-bottom:40px;">
            <div style="width:50%;float:left;">
              <?php the_title(); ?>
            </div>
            <a class="geodir_button" style="float:right;" href="<?php echo get_permalink($pid) ?>"><span style="color: #ffffff !important;" >ร้านค้า</span></a>
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
          <div class="table-responsive">
            <table id="product_table" class="table">
              <thead>
                <th>ชื่อ</th>
                <th>ราคา</th>
                <th>รายละเอียดแบบย่อ</th>
                <th>จำนวน</th>
                <th>ไม่จำกัดจำนวน</th>
                <th>แก้ไขล่าสุดเมื่อ</th>
                <th>แก้ไข</th>
                <th>ลบ</th>
              </thead>
              <tbody>
              <?php

                foreach ($arrProducts as $product) {
                  echo '<tr id="'.$product->id.'">';
                  echo '<td style="text-align:center;">'.$product->name.'</td>';
                  echo '<td style="text-align:center;">'.$product->price.'</td>';
                  echo '<td>'.$product->short_desc.'</td>';
                  echo '<td style="text-align:center;">'.$product->stock.'</td>';
                  echo '<td style="text-align:center;">'.($product->unlimited == '1' ? 'ใช่' : 'ไม่').'</td>';
                  $date = date_create($product->update_date);
                  echo '<td>'.date_format($date, 'd-m-Y H:i:s').'</td>';
                  echo '<td style="text-align:center;"><a class="btn btn-primary btn-block" href="'. home_url('/add-product/') . '?pid='.$pid .'&product_id='.$product->id.'"><span style="color: #ffffff !important;" >แก้ไข</span></a></td>';
                  echo '<td style="text-align:center;"><a class="btn btn-danger btn-block" href="#" data-record-id="'.$product->id.'" data-record-title="'.$product->name.'" data-record-nonce="'.wp_create_nonce( 'delete_product_' . $product->id ).'" data-toggle="modal" data-target="#confirm-delete" ><span style="color: #ffffff !important;" >ลบ</span></a></td>';
                  echo '</tr>';
                }

    		      ?>
              </tbody>
            </table>
          </div>
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
