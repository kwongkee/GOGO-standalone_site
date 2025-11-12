<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "{$data.title|escape='json'}",
  "image": "{if !empty($data.thumb)}https://dtc.gogo198.net{$data.thumb}{else}https://dtc.gogo198.net/public/default.jpg{/if}",
  "datePublished": "{if !empty($data.create_time)}{$data.create_time|date='c'}{else}2025-11-12T00:00:00+08:00{/if}",
  "dateModified": "{if !empty($data.update_time)}{$data.update_time|date='c'}{else}{$data.create_time|date='c'}{/if}",
  "author": {
    "@type": "Organization",
    "name": "GOGO198"
  },
  "publisher": {
    "@type": "Organization",
    "name": "GOGO198",
    "logo": {
      "@type": "ImageObject",
      "url": "https://dtc.gogo198.net/public/logo.png"
    }
  },
  "description": "{$data.intro|strip_tags|truncate=160|escape='json'}",
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": "https://dtc.gogo198.net/?s=index/detail&id={$data.id}"
  }
}
</script>
