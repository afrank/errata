# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.conf.urls import patterns, url, include

#import sizable.post
#from django.contrib.auth.decorators import login_required, permission_required

from api import *

from django.contrib import admin
admin.autodiscover()

urlpatterns = patterns('',
    url(r'^$', Default.as_view(), name='Default'),
    url(r'^admin/', include(admin.site.urls)),
)
