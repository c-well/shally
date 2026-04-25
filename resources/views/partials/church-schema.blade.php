{{--
  Schema.org Church JSON-LD. Google uses this to build the knowledge panel
  (the big card on the right side of the search result) and to qualify
  local-pack eligibility. Include ONLY on the landing page.

  Test any changes in Google's Rich Results Test:
    https://search.google.com/test/rich-results
--}}
@verbatim
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Church",
  "name": "Shalom Seventh-day Adventist Church",
  "alternateName": "The Church of Peace",
  "url": "https://thechurchofpeace.org/",
  "logo": "https://thechurchofpeace.org/og-default.png",
  "image": "https://thechurchofpeace.org/og-default.png",
  "description": "Shalom Seventh-day Adventist Church in the Bronx — a community of believers gathering each Sabbath for worship, Bible study, and prayer.",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "3323 White Plains Road",
    "addressLocality": "Bronx",
    "addressRegion": "NY",
    "postalCode": "10467",
    "addressCountry": "US"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": 40.8621,
    "longitude": -73.8617
  },
  "email": "contact@thechurchofpeace.org",
  "sameAs": [
    "https://www.facebook.com/thechurchofpeace",
    "https://www.youtube.com/c/ShalomSDAChurchBX"
  ],
  "openingHoursSpecification": [
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": "Saturday",
      "opens": "09:30",
      "closes": "13:00",
      "description": "Sabbath School 9:30 AM · Worship Service 11:00 AM"
    },
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": "Saturday",
      "opens": "16:00",
      "closes": "17:00",
      "description": "Bible Class (Zoom)"
    },
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday"],
      "opens": "05:00",
      "closes": "06:00",
      "description": "Hour of Prayer (Zoom)"
    },
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": "Wednesday",
      "opens": "19:00",
      "closes": "20:00",
      "description": "Prayer Meeting (Zoom)"
    }
  ],
  "parentOrganization": {
    "@type": "Organization",
    "name": "Seventh-day Adventist Church",
    "url": "https://www.adventist.org/"
  },
  "event": [
    {
      "@type": "Event",
      "@id": "https://thechurchofpeace.org/#worship",
      "name": "Sabbath Worship Service",
      "description": "Weekly Sabbath worship service — hymns, prayer, and preaching. All welcome.",
      "eventSchedule": {
        "@type": "Schedule",
        "byDay": "https://schema.org/Saturday",
        "startTime": "11:00",
        "endTime": "12:30",
        "repeatFrequency": "P1W"
      },
      "location": {
        "@type": "Place",
        "name": "Shalom SDA Church",
        "address": {
          "@type": "PostalAddress",
          "streetAddress": "3323 White Plains Road",
          "addressLocality": "Bronx",
          "addressRegion": "NY",
          "postalCode": "10467"
        }
      },
      "eventAttendanceMode": "https://schema.org/MixedEventAttendanceMode",
      "organizer": {
        "@type": "Organization",
        "name": "Shalom SDA Church"
      },
      "isAccessibleForFree": true
    }
  ]
}
</script>
@endverbatim
