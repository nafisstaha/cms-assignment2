<?php
/*
Template Name: Post project template
*/
?>


<?php get_header('secondary');?>



<section class="page-wrap">
  <div class="container">
    <h1><?php the_title();?></h1>

    <div class="row">
      <div class="col-lg-12">

        <div class="d-flex justify-content-center align-items-center">
          <a href="#"><i class="fa-brands fa-twitter"
              style="font-size: 60px;background: orange; margin-right:30px;"></i></a>
          <a href="#"> <i class="fa-brands fa-facebook-f"
              style="font-size: 60px;background: orange; margin-right:30px;"></i>
          </a>
          <a href="#"><i class="fa-brands fa-linkedin-in"
              style="font-size: 60px;background: orange;"></i></a>
        </div>
      </div>

    </div>

    <div class="col-lg-12">

      <?php get_template_part('includes/section','content');?>

    </div>



  </div>




  </div>
</section>


<?php get_footer();?>