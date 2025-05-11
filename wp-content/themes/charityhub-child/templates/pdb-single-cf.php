<?php
/*
 * template for displaying a brand page
 *
 * single record template
 */
// get the template object for this record
$this_business = new PDb_Template($this);
?>
<h1 class="brand">
 <?php $this_business->print_field('brand') ?>
</h1>

<h2 class="cf-status">
  <?php $this_business->print_field('brand') ;
      if ( $this_business->get_value('cruelty_free') == true) {
        echo  ' is cruelty-free.';
    } elseif ($this_business->get_value('cruelty_free') == false) {
        echo ' is not cruelty-free.'; }    
?>  </h2>

<p class="cf-status-description">
  <?php if ( $this_business->get_value('cruelty_free') == true) {
      $this_business->print_field('brand') ;
        echo  ' has confirmed that they don\'t test their products and ingredients on animals and that they don\'t sell their products where animal testing is required by law.';
    } elseif ($this_business->get_value('cruelty_free') == false) {
        echo 'This means that this brand tests on animals or finances animal testing. Some brands that fall under this category test on animals where required by law, which means they\'re not cruelty-free.'; } 
?>  </p>

<p class="parent-testing">
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
<h3 class="parent">Parent Company</h3>
<p class="parent1"> <?php echo 'Although ' ;
    $this_business->print_field('brand') ;
    echo ' is cruelty-free, it is owned by ' ;
    $this_business->print_field('parent') ;
    echo ', a parent company that tests on animals.'  ;   ?>  
</p>    <?php endif ?>

<?php // 2: Although [BRAND] is cruelty-free, it is owned by [parent], a parent company that tests on animals.
if (($this_business->get_value('parent_tests') == true) 
    && ($this_business->get_value('cruelty_free') == true) 
    && ($this_business->get_value('parent') == !NULL))  : ?>
<h3 class="parent">Parent Company</h3>
<p class="parent2"> <?php echo 'Although ' ;
    $this_business->print_field('brand') ;
    echo ' is cruelty-free, it is owned by a parent company that tests on animals.'  ;   ?>  
</p>    <?php endif ?>
        
<?php // 3: [BRAND] is not owned by a parent company that tests on animals.
    if (($this_business->get_value('parent_tests') == false) 
         && ($this_business->get_value('cruelty_free') == true) 
         && ($this_business->get_value('parent') == NULL))  : ?>
<h3 class="parent">Parent Company</h3>
<p class="parent3"> <?php $this_business->print_field('brand') ;
    echo ' is not owned by a parent company that tests on animals. ' ;   ?>  
</p>    <?php endif ?>
    
<?php // 4: [BRAND] 's parent company [parent] is cruelty-free.    
if (($this_business->get_value('parent_tests') == false) 
     && ($this_business->get_value('cruelty_free') == true) 
     && ($this_business->get_value('parent') == !NULL))  : ?>
<h3 class="parent">Parent Company</h3>
<p class="parent4"> <?php $this_business->print_field('brand') ;
    echo '\'s parent company ';
    $this_business->print_field('parent');
    echo ' is cruelty-free. ' ;   ?>    
</p>    <?php endif ?>

<?php if (($this_business->get_value('vegan')) == true 
    && ($this_business->get_value('cruelty_free')) == true ) : ?>
<h3 class="vegan">Vegan</h3>
<p class="is_vegan"> <?php $this_business->print_field('brand') ;
    echo ' products are 100% vegan.' ; ?>
</p>    <?php endif ?>

<?php if (($this_business->get_value('vegan')) == false && ($this_business->get_value('cruelty_free')) == true ) : ?>
<h3 class="vegan">Vegan</h3>
<p class="not_vegan"> <?php $this_business->print_field('brand') ;
echo ' is not 100% vegan. You can contact the company for a list of their vegan products.' ; ?>
</p>    <?php endif ?>

<?php if (($this_business->has_content('products')) && ($this_business->get_value('cruelty_free')) == true ) : ?>
<h3 class="products">What they sell</h3>
<p class="products"> <?php $this_business->print_field('products') ?>
</p>    <?php endif ?>

<?php if (($this_business->has_content('distributors')) && ($this_business->get_value('cruelty_free')) == true ) : ?>
<h3 class="distributors">Where to buy</h3>
<p class="distributors"> <?php $this_business->print_field('distributors') ?>
</p>    <?php endif ?>

<h3> <?php    // Website
        $website_url = $this_business->get_field_prop('website', 'link') ;
        if ($this_business->get_value('website') == !null) {
        echo '<a class = "website" href="' . $website_url . '">Website</a>'; }
?> </h3>

<h4 class="other-details">
  <strong>Palm Oil Free: </strong><?php $this_business->print_field('palm_oil_free') ?><br />
  <strong>Organic: </strong><?php $this_business->print_field('organic') ?><br />
  <strong>Bulgarian Product: </strong><?php $this_business->print_field('bg_product') ?><br />
</h4>

<?php if ($this_business->has_content('LB || CCF || PETA') 
    && ($this_business->get_value('cruelty_free') == true)) : ?>
<h3 class="certifications">Certifications</h3>
<p class="certifications-cf"> <?php echo 'This brand is certified Cruelty-Free by ';
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

<div>
<h2 class = "link_to_list">Looking for cruelty-free brands?</h2>
<p>Browse our curated <a href = "/cruelty-free-list">list of cruelty-free brands</a> to find brands that don't test on animals at any point during the production of their products, nor allow suppliers or third parties to test on animals on their behalf. You can search for specific categories to tailor the list to your needs.
</div>