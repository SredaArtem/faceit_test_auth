require('./bootstrap');
import passport from "passport";
import jwt from "jsonwebtoken";
import faceitStrategy from "@amber/passport-faceit";

/* Supported scopes: email, membership, openid, profile */
const scopes = "openid, email, profile";

passport.use(new faceitStrategy({
    authorizationURL: FACEIT_AUTHORIZATION_ENDPOINT,
    tokenURL: FACEIT_TOKEN_ENDPOINT,
    callbackURL: YOUR_CALLBACK_URL,
    clientID: FACEIT_CLIENT_ID,
    clientSecret: FACEIT_CLIENT_SECRET,
    scope: scopes,
    scopeSepartor: ',',
    customHeaders: {
        "Authorization": "Basic ${Buffer.from(
        faceitConfig.oauthClientId + ":" + faceitConfig.oauthClientSecret
).toString("base64")}",
"Content-Type": "application/x-www-form-urlencoded"
}
},
(accessToken, refreshToken, params, profile, done) => {
    const userData = jwt.decode(params.id_token);
    done(null, {
        /* Handle the user data as you wish
        * ...
        */
    });
}
));
