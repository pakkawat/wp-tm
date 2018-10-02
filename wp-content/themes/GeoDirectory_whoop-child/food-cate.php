<?php /* Template Name: All food categories */ ?>
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
              <a href="<?php echo home_url('/places/').'ครัวคุณต๋อย/';?>">ครัวคุณต๋อย</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'เค้กขนมหวาน/';?>">กาแฟ / เค้ก / ของหวาน</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ทั่วไป/';?>">ทั่วไป</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'อาหารจีน/';?>">อาหารจีน</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'อาหารญี่ปุ่น/';?>">อาหารญี่ปุ่น</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'อาหารตะวันตก/';?>">อาหารตะวันตก</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'อาหารทะเล/';?>">อาหารทะเล</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'อาหารนานาชาติ/';?>">อาหารนานาชาติ</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'อาหารฝรั่งเศษ/';?>">อาหารฝรั่งเศษ</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'อาหารสตรีทฟู้ด/';?>">อาหารสตรีทฟู้ด</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'อาหารสเปน/';?>">อาหารสเปน</a>
            </li>
          </ul>
          <ul class="locations_list">
            <li class="region">
              <a href="<?php echo home_url('/places/').'อาหารอิตาเลียน/';?>">อาหารอิตาเลียน</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'อาหารอินเดีย/';?>">อาหารอินเดีย</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'อาหารอิสลาม/';?>">อาหารอิสลาม</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'อาหารอเมริกัน/';?>">อาหารอเมริกัน</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'อาหารเกาหลี/';?>">อาหารเกาหลี</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'อาหารเพื่อสุขภาพ/';?>">อาหารเพื่อสุขภาพ</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'อาหารเวียดนาม/';?>">อาหารเวียดนาม</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'อาหารไทย/';?>">อาหารไทย</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'อาหารเม๊กซิกัน/';?>">อาหารเม๊กซิกัน</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'อาหารเยอรมัน/';?>">อาหารเยอรมัน</a>
            </li>
            <li class="region">
              <a href="<?php echo home_url('/places/').'ธงฟ้า/';?>">ธงฟ้าประชารัฐ</a>
            </li>
			<li class="region">
              <a href="<?php echo home_url('/places/').'สถานบันเทิง/';?>">สถานบันเทิง</a>
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
