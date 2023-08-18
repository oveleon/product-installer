import Installer from "../Installer/Installer";
import State from "../Installer/State";
import {i18n} from "../Installer/Language";

export async function call(url, parameter = {}, cache = false)
{
    // Append default parameter
    parameter.locale = Installer.locale
    parameter.connector = State.get('connector') ?? null

    const props = {
        method: 'POST',
        cache: cache ? "force-cache" : "no-cache",
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(parameter)
    }

    return fetch(url, props)
            .then((response) => {
                // Intercept when the route has been redirected to the login page.
                // In this case the session has expired and a clear error message should be issued.
                if(response.url.includes('login') && response.url.includes('redirect'))
                {
                    throw new Error(i18n('error.session.lost'))
                }

                return response
            })
            .then((response) => response.json())
            .then((data) => data)
}

export async function get(url, header = {}, cache = false)
{
    header['Content-Type'] = 'application/json'

    const props = {
        method: 'GET',
        cache: cache ? "force-cache" : "no-cache",
        headers: header,
    }

    return fetch(url, props)
        .then((response) => response.json())
        .then((data) => data)
}
