<?php

namespace App\Tests\Service;

use App\Entity\Artist;
use App\Entity\Track;
use App\Exception\SoundCloudParseException;
use App\Service\SoundCloudParser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class SoundCloudParserTest extends TestCase
{
    private function testData(): array
    {
        return [
            ['link' => 'https://soundcloud.com/lakeyinspired', 'user_id' => 103470313],
            ['link' => 'https://soundcloud.com/aljoshakonstanty', 'user_id' => 19697305],
            ['link' => 'https://soundcloud.com/birocratic', 'user_id' => 3058637],
            ['link' => 'https://soundcloud.com/dixxy-2', 'user_id' => 95793893],
            ['link' => 'https://soundcloud.com/dekobe', 'user_id' => 102429008],
        ];
    }

    public function testResolveUserID()
    {
        foreach ($this->testData() as &$case) {
            ['link' => $link, 'user_id' => $expectedUserID] = $case;
            $mockResponse = new MockResponse($this->generateUserBody($expectedUserID), ['http_code' => 200]);
            $parser = new SoundCloudParser(new MockHttpClient($mockResponse));
            $userID = $parser->resolveUserID($link);
            $this->assertEquals($expectedUserID, $userID, $link);
        }
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws SoundCloudParseException
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testGetTracks()
    {
        $response = new MockResponse($this->generateTracksBody());
        $parser = new SoundCloudParser(new MockHttpClient($response));
        $tracks = $parser->getTracks(103470313);

        $expectedArtist = (new Artist())
            ->setExtId(103470313)
            ->setUsername('LAKEY INSPIRED')
            ->setFullName('LAKEY')
            ->setCity('Los Angeles')
            ->setFollowersCount(190626);

        $expectedTracks = [
            (new Track())
                ->setArtist($expectedArtist)
                ->setExtId(681078026)
                ->setTitle('Arcade')
                ->setDuration(107790)
                ->setCommentCount(1031)
                ->setPlaybackCount(1375192),
            (new Track())
                ->setArtist($expectedArtist)
                ->setExtId(615426525)
                ->setTitle('By The Pool')
                ->setDuration(155286)
                ->setCommentCount(550)
                ->setPlaybackCount(949595),
            (new Track())
                ->setArtist($expectedArtist)
                ->setExtId(580631754)
                ->setTitle('Overjoyed')
                ->setDuration(160823)
                ->setCommentCount(1207)
                ->setPlaybackCount(1774174),
        ];

        $this->assertCount(3, $tracks);
        for ($i = 0; $i < 3; $i++) {
            $this->assertEquals($tracks[$i], $expectedTracks[$i]);
        }
    }


    private function generateUserBody(int $userID): string {
        return '
...
<script>window.__sc_version="1642757693"</script>
<script>window.__sc_hydration = [{"hydratable":"anonymousId","data":"503825-67644-42972-531605"},{"hydratable":"features","data":{"features":["mobi_use_onetrust_gb","v2_enable_sourcepoint_tcfv2","mobi_social_sharing","v2_use_onetrust_tcfv2_eu1","v2_popular_tags","v2_enable_onetrust","v2_use_onetrust_eu2","v2_use_onetrust_us","v2_tags_recent_tracks","use_recurly_checkout","mobi_enable_onetrust_tcfv2","mobi_use_onetrust_eu1","mobi_use_onetrust_tcfv2_eu2","v2_test_feature_toggle","mobi_use_onetrust_tcfv2_eu1","v2_post_with_caption","mobi_use_onetrust_eu4","featured_artists_banner","v2_repost_redirect_page","v2_use_onetrust_gb","v2_signals_collection","v2_direct_support_link","v2_hq_file_storage_release","v2_use_onetrust_eu4","v2_stories_onboarding","mobi_use_onetrust_eu3","mobi_use_onetrust_elsewhere","v2_use_onetrust_eu3","mobi_use_onetrust_us","v2_oscp_german_tax_fields_support","v2_use_new_connect","v2_use_onetrust_tcfv2_eu2","v2_use_onetrust_eu1","v2_enable_sourcepoint","mobi_use_onetrust_eu2","v2_enable_new_web_errors","v2_use_onetrust_elsewhere","mobi_sign_in_experiment","mobi_enable_onetrust","v2_can_see_insights","mobi_trinity","v2_eg_via_apiweb","v2_enable_pwa","mobi_use_hls_hack","v2_stories","v2_enable_onetrust_tcfv2","v2_enable_tcfv2_consent_string_cache"]}},{"hydratable":"experiments","data":{"v2_listening":{"experiment_name":"google_one_tap","variant_id":2454,"variant_name":"treatment"},"reco_new_users":{"experiment_name":"pfy_plays_part_2","variant_id":2451,"variant_name":"control"}}},{"hydratable":"geoip","data":{"country_code":"RU","country_name":"Russian Federation","region":"66","city":"Saint Petersburg","postal_code":"190098","latitude":59.89830017089844,"longitude":30.261795043945312}},{"hydratable":"privacySettings","data":{"allows_messages_from_unfollowed_users":false,"analytics_opt_in":true,"communications_opt_in":true,"targeted_advertising_opt_in":false,"legislation":[]}},{"hydratable":"user","data":{"avatar_url":"https://i1.sndcdn.com/avatars-zLNKcsw5Bgy45V3d-Hpc1cQ-large.jpg","city":"Mississauga","comments_count":289,"country_code":"CA","created_at":"2014-06-29T21:52:46Z","creator_subscriptions":[{"product":{"id":"creator-pro-unlimited"}}],"creator_subscription":{"product":{"id":"creator-pro-unlimited"}},"description":"Software Developer \u0026 Beatmaker\n\nproud partners of:\n@jazzhopcafe\n@autumntheoryrecords\n@chillselect\n@aruarianmusic","followers_count":8821,"followings_count":249,"first_name":"Julian","full_name":"Julian Saavedra","groups_count":0,"id":' . $userID . ',"kind":"user","last_modified":"2022-01-15T15:24:28Z","last_name":"Saavedra","likes_count":239,"playlist_likes_count":5,"permalink":"dekobe","permalink_url":"https://soundcloud.com/dekobe","playlist_count":5,"reposts_count":null,"track_count":67,"uri":"https://api.soundcloud.com/users/102429008","urn":"soundcloud:users:102429008","username":"DeKobe","verified":true,"visuals":{"urn":"soundcloud:users:102429008","enabled":true,"visuals":[{"urn":"soundcloud:visuals:112133172","entry_time":0,"visual_url":"https://i1.sndcdn.com/visuals-000102429008-VA5Ek9-original.jpg"}],"tracking":null},"badges":{"pro":false,"pro_unlimited":true,"verified":true},"station_urn":"soundcloud:system-playlists:artist-stations:102429008","station_permalink":"artist-stations:102429008","url":"/dekobe"}}];</script>
...
';
    }

    private function generateTracksBody(): string {
        return '{
  "collection": [
    {
      "artwork_url": "https://i1.sndcdn.com/artworks-000597731942-zjsx0p-large.jpg",
      "caption": null,
      "commentable": true,
      "comment_count": 1031,
      "created_at": "2019-09-14T17:32:12Z",
      "description": "LAKEY INSPIRED - Arcade\n\nStream on spotify: open.spotify.com/user/lakeyinspired\n\nOther Links:\nYouTube: https://goo.gl/KnnxH2\nInstagram: www.instagram.com/lakeyinspired/\nPatreon: www.patreon.com/lakeyinspired",
      "downloadable": false,
      "download_count": 0,
      "duration": 107790,
      "full_duration": 107790,
      "embeddable_by": "all",
      "genre": "",
      "has_downloads_left": false,
      "id": 681078026,
      "kind": "track",
      "label_name": null,
      "last_modified": "2022-01-06T07:02:10Z",
      "license": "cc-by-sa",
      "likes_count": 31004,
      "permalink": "arcade",
      "permalink_url": "https://soundcloud.com/lakeyinspired/arcade",
      "playback_count": 1375192,
      "public": true,
      "publisher_metadata": {
        "id": 681078026,
        "urn": "soundcloud:tracks:681078026",
        "artist": "LAKEY INSPIRED",
        "contains_music": true,
        "isrc": "QM42K1902386",
        "writer_composer": "Jordan Reddington"
      },
      "purchase_title": "Free Download",
      "purchase_url": "https://hypeddit.com/track/xbfoxv",
      "release_date": null,
      "reposts_count": 14833,
      "secret_token": null,
      "sharing": "public",
      "state": "finished",
      "streamable": true,
      "tag_list": "",
      "title": "Arcade",
      "track_format": "single-track",
      "uri": "https://api.soundcloud.com/tracks/681078026",
      "urn": "soundcloud:tracks:681078026",
      "user_id": 103470313,
      "visuals": null,
      "waveform_url": "https://wave.sndcdn.com/sc4ztNmBhBVC_m.json",
      "display_date": "2019-09-14T17:32:12Z",
      "media": {
        "transcodings": [
          {
            "url": "https://api-v2.soundcloud.com/media/soundcloud:tracks:681078026/4abb94b0-8514-4fe3-b6c7-6bd4d618958d/stream/hls",
            "preset": "mp3_0_0",
            "duration": 107790,
            "snipped": false,
            "format": {
              "protocol": "hls",
              "mime_type": "audio/mpeg"
            },
            "quality": "sq"
          },
          {
            "url": "https://api-v2.soundcloud.com/media/soundcloud:tracks:681078026/4abb94b0-8514-4fe3-b6c7-6bd4d618958d/stream/progressive",
            "preset": "mp3_0_0",
            "duration": 107790,
            "snipped": false,
            "format": {
              "protocol": "progressive",
              "mime_type": "audio/mpeg"
            },
            "quality": "sq"
          },
          {
            "url": "https://api-v2.soundcloud.com/media/soundcloud:tracks:681078026/bf3fe3a3-3e31-4593-959e-7e290f6b6dcb/stream/hls",
            "preset": "opus_0_0",
            "duration": 107723,
            "snipped": false,
            "format": {
              "protocol": "hls",
              "mime_type": "audio/ogg; codecs=\"opus\""
            },
            "quality": "sq"
          }
        ]
      },
      "station_urn": "soundcloud:system-playlists:track-stations:681078026",
      "station_permalink": "track-stations:681078026",
      "track_authorization": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJnZW8iOiJSVSIsInN1YiI6IiIsInJpZCI6ImVhN2UwN2M2LTZmMTEtNDg4MC04MTg3LThhYWViZDNjOWI2MCIsImlhdCI6MTY0Mjk2MDA1OX0.1j2T2ZWzFcGellUVNkZfO1qoEb-3bZPxGRho6rfiRkE",
      "monetization_model": "NOT_APPLICABLE",
      "policy": "ALLOW",
      "user": {
        "avatar_url": "https://i1.sndcdn.com/avatars-wRE7zHlr4wuGZ9Ya-na6tuQ-large.jpg",
        "first_name": "LAKEY",
        "followers_count": 190626,
        "full_name": "LAKEY",
        "id": 103470313,
        "kind": "user",
        "last_modified": "2021-04-26T23:46:00Z",
        "last_name": "",
        "permalink": "lakeyinspired",
        "permalink_url": "https://soundcloud.com/lakeyinspired",
        "uri": "https://api.soundcloud.com/users/103470313",
        "urn": "soundcloud:users:103470313",
        "username": "LAKEY INSPIRED",
        "verified": true,
        "city": "Los Angeles",
        "country_code": null,
        "badges": {
          "pro": false,
          "pro_unlimited": true,
          "verified": true
        },
        "station_urn": "soundcloud:system-playlists:artist-stations:103470313",
        "station_permalink": "artist-stations:103470313"
      }
    },
    {
      "artwork_url": "https://i1.sndcdn.com/artworks-000529718892-gxgmld-large.jpg",
      "caption": null,
      "commentable": true,
      "comment_count": 550,
      "created_at": "2019-05-04T01:02:49Z",
      "description": "LAKEY INSPIRED - By The Pool\n\nStream on spotify: open.spotify.com/user/lakeyinspired\n\nFollow me on:\nSpotify: open.spotify.com/user/lakeyinspired\nYouTube: https://goo.gl/KnnxH2\nInstagram: www.instagram.com/lakeyinspired/\nPatreon: www.patreon.com/lakeyinspired",
      "downloadable": false,
      "download_count": 0,
      "duration": 155286,
      "full_duration": 155286,
      "embeddable_by": "all",
      "genre": "",
      "has_downloads_left": false,
      "id": 615426525,
      "kind": "track",
      "label_name": null,
      "last_modified": "2022-01-07T04:57:45Z",
      "license": "cc-by-sa",
      "likes_count": 21849,
      "permalink": "by-the-pool",
      "permalink_url": "https://soundcloud.com/lakeyinspired/by-the-pool",
      "playback_count": 949595,
      "public": true,
      "publisher_metadata": {
        "id": 615426525,
        "urn": "soundcloud:tracks:615426525",
        "artist": "LAKEY INSPIRED",
        "contains_music": true,
        "isrc": "QM42K1996377",
        "writer_composer": "Jordan Reddington"
      },
      "purchase_title": "Free Download",
      "purchase_url": "https://hypeddit.com/track/wiumfq",
      "release_date": null,
      "reposts_count": 10899,
      "secret_token": null,
      "sharing": "public",
      "state": "finished",
      "streamable": true,
      "tag_list": "\"LAKEY INSPIRED\"",
      "title": "By The Pool",
      "track_format": "single-track",
      "uri": "https://api.soundcloud.com/tracks/615426525",
      "urn": "soundcloud:tracks:615426525",
      "user_id": 103470313,
      "visuals": null,
      "waveform_url": "https://wave.sndcdn.com/rdmcZfRGBizw_m.json",
      "display_date": "2019-05-04T01:02:49Z",
      "media": {
        "transcodings": [
          {
            "url": "https://api-v2.soundcloud.com/media/soundcloud:tracks:615426525/9daafa49-e406-4256-8b8a-982bc1480168/stream/hls",
            "preset": "mp3_0_0",
            "duration": 155286,
            "snipped": false,
            "format": {
              "protocol": "hls",
              "mime_type": "audio/mpeg"
            },
            "quality": "sq"
          },
          {
            "url": "https://api-v2.soundcloud.com/media/soundcloud:tracks:615426525/9daafa49-e406-4256-8b8a-982bc1480168/stream/progressive",
            "preset": "mp3_0_0",
            "duration": 155286,
            "snipped": false,
            "format": {
              "protocol": "progressive",
              "mime_type": "audio/mpeg"
            },
            "quality": "sq"
          },
          {
            "url": "https://api-v2.soundcloud.com/media/soundcloud:tracks:615426525/0296e297-7d78-49dd-863a-c3cfcbb547cc/stream/hls",
            "preset": "opus_0_0",
            "duration": 155224,
            "snipped": false,
            "format": {
              "protocol": "hls",
              "mime_type": "audio/ogg; codecs=\"opus\""
            },
            "quality": "sq"
          }
        ]
      },
      "station_urn": "soundcloud:system-playlists:track-stations:615426525",
      "station_permalink": "track-stations:615426525",
      "track_authorization": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJnZW8iOiJSVSIsInN1YiI6IiIsInJpZCI6IjgyM2E4ZTRlLTk3OTktNDYwMC1iZDRiLWUxODkwZDkzNzA5MyIsImlhdCI6MTY0Mjk2MDA1OX0.0C6AoHZ12kMtXk2HuKoGX_BAPx-6GLnXyQsPzboLoVQ",
      "monetization_model": "NOT_APPLICABLE",
      "policy": "ALLOW",
      "user": {
        "avatar_url": "https://i1.sndcdn.com/avatars-wRE7zHlr4wuGZ9Ya-na6tuQ-large.jpg",
        "first_name": "LAKEY",
        "followers_count": 190626,
        "full_name": "LAKEY",
        "id": 103470313,
        "kind": "user",
        "last_modified": "2021-04-26T23:46:00Z",
        "last_name": "",
        "permalink": "lakeyinspired",
        "permalink_url": "https://soundcloud.com/lakeyinspired",
        "uri": "https://api.soundcloud.com/users/103470313",
        "urn": "soundcloud:users:103470313",
        "username": "LAKEY INSPIRED",
        "verified": true,
        "city": "Los Angeles",
        "country_code": null,
        "badges": {
          "pro": false,
          "pro_unlimited": true,
          "verified": true
        },
        "station_urn": "soundcloud:system-playlists:artist-stations:103470313",
        "station_permalink": "artist-stations:103470313"
      }
    },
    {
      "artwork_url": "https://i1.sndcdn.com/artworks-000494129691-yl2ubl-large.jpg",
      "caption": null,
      "commentable": true,
      "comment_count": 1207,
      "created_at": "2019-02-24T20:16:37Z",
      "description": "LAKEY INSPIRED - Overjoyed\n\nStream on spotify: open.spotify.com/user/lakeyinspired\n\nFollow me on:\nSpotify: open.spotify.com/user/lakeyinspired\nYouTube: https://goo.gl/KnnxH2\nInstagram: www.instagram.com/lakeyinspired/\nPatreon: www.patreon.com/lakeyinspired",
      "downloadable": false,
      "download_count": 0,
      "duration": 160823,
      "full_duration": 160823,
      "embeddable_by": "all",
      "genre": "",
      "has_downloads_left": false,
      "id": 580631754,
      "kind": "track",
      "label_name": null,
      "last_modified": "2022-01-05T00:23:08Z",
      "license": "cc-by-sa",
      "likes_count": 42182,
      "permalink": "overjoyed",
      "permalink_url": "https://soundcloud.com/lakeyinspired/overjoyed",
      "playback_count": 1774174,
      "public": true,
      "publisher_metadata": {
        "id": 580631754,
        "urn": "soundcloud:tracks:580631754",
        "artist": "LAKEY INSPIRED",
        "contains_music": true,
        "isrc": "QM42K1987980",
        "writer_composer": "Jordan Reddington"
      },
      "purchase_title": "Free Download",
      "purchase_url": "https://hypeddit.com/track/2hh5tp",
      "release_date": null,
      "reposts_count": 20752,
      "secret_token": null,
      "sharing": "public",
      "state": "finished",
      "streamable": true,
      "tag_list": "",
      "title": "Overjoyed",
      "track_format": "single-track",
      "uri": "https://api.soundcloud.com/tracks/580631754",
      "urn": "soundcloud:tracks:580631754",
      "user_id": 103470313,
      "visuals": null,
      "waveform_url": "https://wave.sndcdn.com/kVwLgAe6VTUw_m.json",
      "display_date": "2019-02-24T20:16:37Z",
      "media": {
        "transcodings": [
          {
            "url": "https://api-v2.soundcloud.com/media/soundcloud:tracks:580631754/0801258f-1b4d-4e7c-9d46-4192f2528e47/stream/hls",
            "preset": "mp3_0_0",
            "duration": 160823,
            "snipped": false,
            "format": {
              "protocol": "hls",
              "mime_type": "audio/mpeg"
            },
            "quality": "sq"
          },
          {
            "url": "https://api-v2.soundcloud.com/media/soundcloud:tracks:580631754/0801258f-1b4d-4e7c-9d46-4192f2528e47/stream/progressive",
            "preset": "mp3_0_0",
            "duration": 160823,
            "snipped": false,
            "format": {
              "protocol": "progressive",
              "mime_type": "audio/mpeg"
            },
            "quality": "sq"
          },
          {
            "url": "https://api-v2.soundcloud.com/media/soundcloud:tracks:580631754/51134278-3122-4489-923f-934781573192/stream/hls",
            "preset": "opus_0_0",
            "duration": 160761,
            "snipped": false,
            "format": {
              "protocol": "hls",
              "mime_type": "audio/ogg; codecs=\"opus\""
            },
            "quality": "sq"
          }
        ]
      },
      "station_urn": "soundcloud:system-playlists:track-stations:580631754",
      "station_permalink": "track-stations:580631754",
      "track_authorization": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJnZW8iOiJSVSIsInN1YiI6IiIsInJpZCI6ImNmODQ2NWZmLWJjZDgtNGExNy1hNjcyLTBkZGNiYTZhNTM3YiIsImlhdCI6MTY0Mjk2MDA1OX0.hF1BaBS1jyHiE00Y2xoMvdA5Edc8iJGKhpLFLl-wBdc",
      "monetization_model": "NOT_APPLICABLE",
      "policy": "ALLOW",
      "user": {
        "avatar_url": "https://i1.sndcdn.com/avatars-wRE7zHlr4wuGZ9Ya-na6tuQ-large.jpg",
        "first_name": "LAKEY",
        "followers_count": 190626,
        "full_name": "LAKEY",
        "id": 103470313,
        "kind": "user",
        "last_modified": "2021-04-26T23:46:00Z",
        "last_name": "",
        "permalink": "lakeyinspired",
        "permalink_url": "https://soundcloud.com/lakeyinspired",
        "uri": "https://api.soundcloud.com/users/103470313",
        "urn": "soundcloud:users:103470313",
        "username": "LAKEY INSPIRED",
        "verified": true,
        "city": "Los Angeles",
        "country_code": null,
        "badges": {
          "pro": false,
          "pro_unlimited": true,
          "verified": true
        },
        "station_urn": "soundcloud:system-playlists:artist-stations:103470313",
        "station_permalink": "artist-stations:103470313"
      }
    }
  ],
  "next_href": "https://api-v2.soundcloud.com/users/103470313/tracks?offset=2017-07-30T23%3A22%3A22.000Z%2Ctracks%2C00335567654&limit=20&representation=",
  "query_urn": null
}';
    }
}
