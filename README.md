Introduction
======
[![MIT licensed](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE) 
[![Travis Build Status](https://travis-ci.com/famoser/vseth-newsletter.svg?branch=master)](https://travis-ci.com/famoser/vseth-newsletter)
[![Scrutinizer](https://scrutinizer-ci.com/g/famoser/vseth-newsletter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/famoser/vseth-newsletter)

Goals:
 - allow organisations to create newsletter entries
 - curate those entries in the admin area
 - send the newsletter

Testing:
 - request `/login/code/1234` to login as an organisation
 - use `ia@vseth.ethz.ch` `secret` at `/login` to login as an administrator

Release:
 - execute `./vendor/bin/agnes release v1.0 master` to create release `v1.0` from master (ensure the GITHUB_AUTH_TOKEN in `.env` is set)
