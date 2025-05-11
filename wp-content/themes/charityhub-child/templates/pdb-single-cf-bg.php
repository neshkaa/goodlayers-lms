<?php
/*
 * template for displaying a business detailed view
 *
 * single record template
 */
// get the template object for this record
$this_business = new PDb_Template($this);
?>
<h1 class="brand">
 <?php $this_business->print_field('brand') ?>
</h1>

<h1 class="cf-status">
  <?php $this_business->print_field('brand') ;
      if ( $this_business->get_value('cruelty_free') == true) {
        echo  ' е без жестокост.';
    } elseif ($this_business->get_value('cruelty_free') == false) {
        echo ' НЕ е без жестокост.'; }    ?>
</h1>

<p class="cf-status-description">
  <?php if ( $this_business->get_value('cruelty_free') == true) {
      $this_business->print_field('brand') ;
        echo  ' са потвърдили, че не тестват върху животни по време на производствтото на техните прoдукти. Също така, не продават продуктите си там, където закона изисква тестване върху животни.';
    } elseif ($this_business->get_value('cruelty_free') == false) {
        echo 'Това означава, че тази марка тества върху животни или финансира опити върху животни. Някои марки в тази категория тестват върху живтони там, където закона го изисква, което означава че не са без жестокост.'; }    ?>
</p>

  <?php 
  
/* Parent
                         | Parent tests |
   Cruelty-Free = TRUE   |   ˅  |  ˅    |
                         | TRUE | FALSE |
-------------------------+------+-------|
          >     NULL     |   1  |   3   |
 Parent   ---------------+------+-------|
          >     !NULL    |   2  |   4   |
-------------------------+------+-------| */
  
// 1: Although [BRAND] is cruelty-free, it is owned by a parent company that tests on animals.
if (($this_business->get_value('parent_tests') == true) 
    && ($this_business->get_value('cruelty_free') == true) 
    && ($this_business->get_value('parent') == NULL))  : ?>
<h3 class="parent">Компания майка</h3>
<p class="parent1"> <?php echo 'Въпреки че ' ;
    $this_business->print_field('brand') ;
    echo ' е без жестокост, марката е собственост на ' ;
    $this_business->print_field('parent') ;
    echo ', компания, която тества върху животни.'  ;   ?>  
</p>    <?php endif ?>

<?php // 2: Although [BRAND] is cruelty-free, it is owned by [parent], a parent company that tests on animals.
if (($this_business->get_value('parent_tests') == true) 
    && ($this_business->get_value('cruelty_free') == true) 
    && ($this_business->get_value('parent') == !NULL))  : ?>
<h3 class="parent">Компания майка</h3>
<p class="parent2"> <?php echo 'Въпреки че ' ;
    $this_business->print_field('brand') ;
    echo ' е без жестокост, марката е собственост на компания, която тества върху животни.'  ;   ?>  
</p>    <?php endif ?>
        
<?php // 3: [BRAND] is not owned by a parent company that tests on animals.
    if (($this_business->get_value('parent_tests') == false) 
         && ($this_business->get_value('cruelty_free') == true) 
         && ($this_business->get_value('parent') == NULL))  : ?>
<h3 class="parent">Компания майка</h3>
<p class="parent3"> <?php $this_business->print_field('brand') ;
    echo ' не е собственост на компнания, която тества върху животни. ' ;   ?>  
</p>    <?php endif ?>
    
<?php // 4: [BRAND] 's parent company [parent] is cruelty-free.    
if (($this_business->get_value('parent_tests') == false) 
     && ($this_business->get_value('cruelty_free') == true) 
     && ($this_business->get_value('parent') == !NULL))  : ?>
<h3 class="parent">Компания майка</h3>
<p class="parent4"> <?php $this_business->print_field('brand') ;
    echo ', компанията майка на ';
    $this_business->print_field('parent');
    echo ' е без жестокост. ' ;   ?>    
</p>    <?php endif ?>

<?php if (($this_business->get_value('vegan')) == true 
    && ($this_business->get_value('cruelty_free')) == true ) : ?>
<h3 class="vegan">Веган</h3>
<p class="is_vegan"> <?php echo 'Продуктите на ';
    $this_business->print_field('brand') ;
    echo ' са 100% веган (не съдържат животински съставки).' ; ?>
</p>    <?php endif ?>

<?php if (($this_business->get_value('vegan')) == false && ($this_business->get_value('cruelty_free')) == true ) : ?>
<h3 class="vegan">Веган</h3>
<p class="not_vegan"> <?php $this_business->print_field('brand') ;
echo ' не е 100% веган. Можете да се свържете с тях за да получите списък на продуктите им без животински съставки.' ; ?>
</p>    <?php endif ?>

<?php if (($this_business->has_content('products')) && ($this_business->get_value('cruelty_free')) == true ) : ?>
<h3 class="products">Асортимент</h3>
<p class="products"> <?php $this_business->print_field('products') ?>
</p>    <?php endif ?>

<?php if (($this_business->has_content('distributors')) && ($this_business->get_value('cruelty_free')) == true ) : ?>
<h3 class="distributors">Къде се продава</h3>
<p class="distributors"> <?php $this_business->print_field('distributors') ?>
</p>    <?php endif ?>

<h3> <?php    // Website
        $website_url = $this_business->get_field_prop('website', 'link') ;
        if ($this_business->get_value('website') == !null) {
        echo '<a class = "website" href="' . $website_url . '">Уебсайт</a>'; }
?> </h3>

<h4 class="other-details">
  <strong>Без палмово масло: </strong><?php $this_business->print_field('palm_oil_free') ?><br />
  <strong>Organic: </strong><?php $this_business->print_field('organic') ?><br />
  <strong>Български продукт: </strong><?php $this_business->print_field('bg_product') ?><br />
</h4>

<?php if ($this_business->has_content('LB || CCF || PETA') 
    && ($this_business->get_value('cruelty_free') == true)) : ?>
<h3 class="certifications">Сертификации</h3>
<p class="certifications-cf"> <?php echo 'Тази марка е сертифицирана без жесоткост от ';
$this_business->print_field('LB') ;
$this_business->print_field('CCF') ;
$this_business->print_field('PETA') ;   ?>  
</p>    <?php endif ?>

<div class="wrap <?php echo $this->wrap_class ?>">
  <?php if ($this_business->has_content('photo')) : ?>
    <div class="business-photo">
      <?php $this_business->print_field('photo') ?>
    </div>
  <?php endif ?>
  <div class="business-info one-half">

  </div>
  <?php // show the "More Details" data as a loop
  echo do_shortcode('[pdb_single template=loop groups=certifications]'); ?>
</div>
