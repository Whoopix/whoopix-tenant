<?php
/* ============================================================================
 * Whoopix flagship NEUTRAL footer (new-site default theme).
 * Content-driven (site name, nav, contact from the tenant DB) — no Site /
 * third-party branding. Pairs with includes/header-wpx.php; reuses wpx_chrome().
 * Also emits the shared theme JS (sticky header, mobile drawer, scroll reveal).
 * The original includes/footer.php (Site) is left untouched.
 * ==========================================================================*/

if (!function_exists('wpx_chrome')) {
    // Header wasn't included (defensive): pull the helper so the footer still works.
    require __DIR__ . '/header-wpx.php';
}
$W = wpx_chrome();
$year = date('Y');
?>
<footer class="wpx-footer">
  <div class="wpx-container">
    <div class="wpx-footer__grid">
      <div>
        <div class="wpx-footer__brand"><span class="wpx-logo__mark"><?= wpx_e($W['initial']) ?></span> <?= wpx_e($W['name']) ?></div>
        <p>שירות מקצועי, אישי ואמין — מחויבים לאיכות ולתוצאה הטובה ביותר עבורכם.</p>
        <div class="wpx-soc">
          <a href="#" aria-label="Facebook"><?= wpx_icon('<path d="M14 9h3V6h-3c-1.7 0-3 1.3-3 3v2H8v3h3v6h3v-6h3l1-3h-4V9c0-.6.4-1 1-1z"/>') ?></a>
          <a href="#" aria-label="Instagram"><?= wpx_icon('<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>') ?></a>
          <a href="#" aria-label="WhatsApp"><?= wpx_icon('<path d="M20 12a8 8 0 0 1-11.9 7L4 20l1-4.1A8 8 0 1 1 20 12z"/>') ?></a>
        </div>
      </div>
      <div>
        <h4>ניווט</h4>
        <ul><?php foreach (array_slice($W['menu'], 0, 6) as $m): ?><li><a href="<?= wpx_e($m['link']) ?>"><?= wpx_e($m['title']) ?></a></li><?php endforeach; ?></ul>
      </div>
      <div>
        <h4>מידע</h4>
        <ul>
          <li><a href="index.php">דף הבית</a></li>
          <li><a href="<?= wpx_e($W['cta']) ?>">צור קשר</a></li>
        </ul>
      </div>
      <div>
        <h4>יצירת קשר</h4>
        <?php if ($W['phone']): ?><div class="wpx-footer__c"><span class="ic"><?= wpx_icon($W['ic']['phone']) ?></span> <a href="tel:<?= wpx_e($W['phone']) ?>"><?= wpx_e($W['phone']) ?></a></div><?php endif; ?>
        <?php if ($W['email']): ?><div class="wpx-footer__c"><span class="ic"><?= wpx_icon($W['ic']['mail']) ?></span> <a href="mailto:<?= wpx_e($W['email']) ?>"><?= wpx_e($W['email']) ?></a></div><?php endif; ?>
        <?php if ($W['addr']): ?><div class="wpx-footer__c"><span class="ic"><?= wpx_icon($W['ic']['pin']) ?></span> <?= wpx_e($W['addr']) ?></div><?php endif; ?>
        <?php if (!$W['phone'] && !$W['email'] && !$W['addr']): ?><p style="color:#94a3b8;">נשמח לעמוד לרשותכם בכל שאלה.</p><?php endif; ?>
      </div>
    </div>
    <div class="wpx-footer__bar">
      <span>© <?= wpx_e($year) ?> <?= wpx_e($W['name']) ?> · כל הזכויות שמורות</span>
      <span>נבנה באמצעות <a href="https://whoopix.ai" target="_blank" rel="noopener">Whoopix</a></span>
    </div>
  </div>
</footer>
<script>
(function(){
  var h=document.getElementById('wpxHeader');
  if(h){var s=function(){h.classList.toggle('is-stuck',window.scrollY>8);};s();window.addEventListener('scroll',s,{passive:true});}
  var b=document.getElementById('wpxBurger'),d=document.getElementById('wpxDrawer'),bk=document.getElementById('wpxDrawerBack'),c=document.getElementById('wpxDrawerClose');
  function open(){d&&d.classList.add('show');bk&&bk.classList.add('show');document.body.style.overflow='hidden';}
  function close(){d&&d.classList.remove('show');bk&&bk.classList.remove('show');document.body.style.overflow='';}
  b&&b.addEventListener('click',open);c&&c.addEventListener('click',close);bk&&bk.addEventListener('click',close);
  d&&d.querySelectorAll('a').forEach(function(a){a.addEventListener('click',close);});
  document.addEventListener('keydown',function(e){if(e.key==='Escape')close();});
  var els=document.querySelectorAll('.wpx-reveal');
  if('IntersectionObserver' in window){
    var io=new IntersectionObserver(function(en){en.forEach(function(x){if(x.isIntersecting){x.target.classList.add('is-in');io.unobserve(x.target);}});},{threshold:.12,rootMargin:'0px 0px -8% 0px'});
    els.forEach(function(el,i){el.style.transitionDelay=(Math.min(i,6)*60)+'ms';io.observe(el);});
  } else { els.forEach(function(el){el.classList.add('is-in');}); }
})();
</script>
