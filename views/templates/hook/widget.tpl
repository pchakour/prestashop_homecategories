{if $custom_css}
<style>
{$custom_css nofilter}
</style>
{/if}

<section class="home-categories featured-products clearfix">
  <h2 class="h2 products-section-title text-uppercase">{l s='Our packs by theme' mod='pc_homecategories'}</h2>
  <div class="home-categories-grid">
    {foreach from=$home_categories item=cat}
      <div class="home-category">
        <a href="{$cat.link}" title="{$cat.name|escape:'html':'UTF-8'}">
          <img src="{$cat.image_url}" alt="{$cat.name|escape:'html':'UTF-8'}" class="img-fluid" />
          <h3>{$cat.name|escape:'html':'UTF-8'}</h3>
        </a>
      </div>
    {/foreach}
  </div>
</section>
