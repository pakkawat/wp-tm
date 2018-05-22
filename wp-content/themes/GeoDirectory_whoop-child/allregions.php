<?php /* Template Name: Show all regions */ ?>
<?php get_header(); ?>

<div id="geodir_wrapper" class="geodir-single">
  <?php //geodir_breadcrumb();?>
  <div class="clearfix geodir-common">
    <div id="geodir_content" class="" role="main" style="width: 100%">
      <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class( 'cf' ); ?> role="article">
        <header class="article-header">
          <h1 class="page-title entry-title" itemprop="headline">
            <?php the_title(); ?>
          </h1>
          <?php /*<p class="byline vcard"> <?php printf( __( 'Posted <time class="updated" datetime="%1$s" >%2$s</time> by <span class="author">%3$s</span>', GEODIRECTORY_FRAMEWORK ), get_the_time('c'), get_the_time(get_option('date_format')), get_the_author_link( get_the_author_meta( 'ID' ) )); ?> </p> */?>
        </header>
        <?php // end article header ?>
        <section class="entry-content cf" itemprop="articleBody">
          <ul class="locations_list">
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/กรุงเทพมหานคร/';?>">กรุงเทพมหานคร</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/กระบี่/';?>">กระบี่</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/กาญจนบุรี/';?>">กาญจนบุรี</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/กาฬสินธุ์/';?>">กาฬสินธุ์</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/กำแพงเพชร/';?>">กำแพงเพชร</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/ขอนแก่น/';?>">ขอนแก่น</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/จันทบุรี/';?>">จันทบุรี</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/ฉะเชิงเทรา/';?>">ฉะเชิงเทรา</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/ชลบุรี/';?>">ชลบุรี</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/ชัยนาท/';?>">ชัยนาท</a>
            </li>
          </ul>
          <ul class="locations_list">
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/ชัยภูมิ/';?>">ชัยภูมิ</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/ชุมพร/';?>">ชุมพร</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/เชียงราย/';?>">เชียงราย</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/เชียงใหม่/';?>">เชียงใหม่</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/ตรัง/';?>">ตรัง</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/ตราด/';?>">ตราด</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/ตาก/';?>">ตาก</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/นครนายก/';?>">นครนายก</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/นครปฐม/';?>">นครปฐม</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/นครพนม/';?>">นครพนม</a>
            </li>
          </ul>
          <ul class="locations_list">
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/นครราชสีมา/';?>">นครราชสีมา</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/นครศรีธรรมราช/';?>">นครศรีธรรมราช</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/นครสวรรค์/';?>">นครสวรรค์</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/นนทบุรี/';?>">นนทบุรี</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/นราธิวาส/';?>">นราธิวาส</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/น่าน/';?>">น่าน</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/บึงกาฬ/';?>">บึงกาฬ</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/บุรีรัมย์/';?>">บุรีรัมย์</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/ปทุมธานี/';?>">ปทุมธานี</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/ประจวบคีรีขันธ์/';?>">ประจวบคีรีขันธ์</a>
            </li>
          </ul>
          <ul class="locations_list">
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/ปราจีนบุรี/';?>">ปราจีนบุรี</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/ปัตตานี/';?>">ปัตตานี</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/พระนครศรีอยุธยา/';?>">พระนครศรีอยุธยา</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/พังงา/';?>">พังงา</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/พัทลุง/';?>">พัทลุง</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/พิจิตร/';?>">พิจิตร</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/พิษณุโลก/';?>">พิษณุโลก</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/เพชรบุรี/';?>">เพชรบุรี</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/เพชรบูรณ์/';?>">เพชรบูรณ์</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/แพร่/';?>">แพร่</a>
            </li>
          </ul>
          <ul class="locations_list">
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/พะเยา/';?>">พะเยา</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/ภูเก็ต/';?>">ภูเก็ต</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/มหาสารคาม/';?>">มหาสารคาม</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/มุกดาหาร/';?>">มุกดาหาร</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/แม่ฮ่องสอน/';?>">แม่ฮ่องสอน</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/ยะลา/';?>">ยะลา</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/ยโสธร/';?>">ยโสธร</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/ร้อยเอ็ด/';?>">ร้อยเอ็ด</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/ระนอง/';?>">ระนอง</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/ระยอง/';?>">ระยอง</a>
            </li>
          </ul>
          <ul class="locations_list">
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/ราชบุรี/';?>">ราชบุรี</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/ลพบุรี/';?>">ลพบุรี</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/ลำปาง/';?>">ลำปาง</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/ลำพูน/';?>">ลำพูน</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/เลย/';?>">เลย</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/ศรีสะเกษ/';?>">ศรีสะเกษ</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/สกลนคร/';?>">สกลนคร</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/สงขลา/';?>">สงขลา</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/สตูล/';?>">สตูล</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/สมุทรปราการ/';?>">สมุทรปราการ</a>
            </li>
          </ul>
          <ul class="locations_list">
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/สมุทรสงคราม/';?>">สมุทรสงคราม</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/สมุทรสาคร/';?>">สมุทรสาคร</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/สระแก้ว/';?>">สระแก้ว</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/สระบุรี/';?>">สระบุรี</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/สิงห์บุรี/';?>">สิงห์บุรี</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/สุโขทัย/';?>">สุโขทัย</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/สุพรรณบุรี/';?>">สุพรรณบุรี</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/สุราษฎร์ธานี/';?>">สุราษฎร์ธานี</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/สุรินทร์/';?>">สุรินทร์</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/หนองคาย/';?>">หนองคาย</a>
            </li>
          </ul>
          <ul class="locations_list">
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/หนองบัวลำภู/';?>">หนองบัวลำภู</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/อ่างทอง/';?>">อ่างทอง</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/อุดรธานี/';?>">อุดรธานี</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/อุทัยธานี/';?>">อุทัยธานี</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/อุตรดิตถ์/';?>">อุตรดิตถ์</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/อุบลราชธานี/';?>">อุบลราชธานี</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ประเทศไทย/อำนาจเจริญ/';?>">อำนาจเจริญ</a>
            </li>
          </ul>
        </section>
        <?php // end article section ?>
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
