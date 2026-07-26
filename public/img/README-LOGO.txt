GCSM IMAGES
===========

The app looks for these files in  public/img/ :

1) GCSM.png        — the company logo (square wheel/anchor mark).
                     Shown in the sidebar and on the login page.
                     ✅ Already placed.

2) login-bg.jpg    — the background photo for the login page (e.g. the ship photo).
                     If missing, the login page falls back to an elegant navy gradient.

WHERE TO PUT THEM
-----------------
For the running app (instant):
   F:\Claude\GCSM\GCSM-Software\gcsm-app\public\img\GCSM.png
   F:\Claude\GCSM\GCSM-Software\gcsm-app\public\img\login-bg.jpg
   Then hard-refresh (Ctrl+Shift+R).

To keep them across rebuilds, also copy to:
   F:\Claude\GCSM\GCSM-Software\overlay\public\img\
   (RUN-GCSM.bat / UPDATE-GCSM.bat deploy this folder.)

Recommended: logo = square PNG (256–512px, transparent/white bg);
             login-bg = wide JPG (≥1920px) landscape photo.
