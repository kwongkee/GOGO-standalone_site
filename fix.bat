cd C:\Users\gogo\project\github项目\独立站\GOGO-standalone_site
Expand-Archive GOGO-SEO-Ultimate-Patch-2025.zip -DestinationPath .
@' (cleaned patch content) '@ | Out-File -FilePath "cleaned_seo.patch" -Encoding UTF8
git apply cleaned_seo.patch
echo "本地修复完成！"