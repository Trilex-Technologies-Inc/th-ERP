package therp.util;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;
import java.util.StringTokenizer;

public class Request 
{
    private String script;
    private Map params = new HashMap();
    
    public Request(String script)
    {
        this.script = script;
    }
    
    public void setParam(String name, Object value)
    {
        params.put(name, value);
    }
    
    public void setAction(String action)
    {
        setParam("action", action);
    }
    
    private String getUrl()
    {
        StringBuilder url = new StringBuilder();
        url.append("http://" + Context.host + "/" + Context.path + "/" + script + "?");
        url.append("&user=" + Context.user);
        url.append("&pwd=" + Context.pwd);
        return url.toString();
    }
    
    public Iterator get()
        throws Exception
    {   
        StringBuilder url = new StringBuilder(getUrl());
        Iterator i = params.keySet().iterator();
        while (i.hasNext()) {
            String key = (String) i.next();
            Object value = params.get(key);
            url.append("&" + key + "=" + value);
        }
        return new ResponseIterator(new URL(url.toString()));
    }
    
    public String post() throws Exception {
        HttpURLConnection conn = (HttpURLConnection) new URL(getUrl())
            .openConnection();
        conn.setRequestMethod("POST");
        conn.setDoInput(true);
        conn.setDoOutput(true);
        OutputStreamWriter writer = new OutputStreamWriter(conn
            .getOutputStream());
        Iterator i = params.keySet().iterator();
        while (i.hasNext()) {
            String key = (String) i.next();
            Object value = params.get(key);
            writer.write(key + "=" + value + "&");
        }
        writer.write("\n");
        writer.close();
        BufferedReader reader = new BufferedReader(new InputStreamReader(conn
            .getInputStream()));
        String line = reader.readLine();
        StringBuilder responseBuilder = new StringBuilder();
        while (line != null) {
            responseBuilder.append(line);
            line = reader.readLine();
        }
        String response = responseBuilder.toString();
        System.err.println("Response: " + response);
        if (response.startsWith("ERROR:"))
            throw new RuntimeException(response.substring(6));
        StringTokenizer tokenizer = new StringTokenizer(response, ";");
        response = tokenizer.nextToken();
        return response;
    }
    

}
