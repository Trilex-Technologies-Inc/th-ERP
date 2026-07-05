package therp.util;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.URL;
import java.net.URLConnection;
import java.util.Iterator;

public class ResponseIterator implements Iterator
{
    private URLConnection conn;
    private BufferedReader reader;
    private String nextLine;
    
    public ResponseIterator(URL url)
        throws Exception
    {
        conn = url.openConnection();
        reader = new BufferedReader(new InputStreamReader(conn.getInputStream()));
        nextLine = reader.readLine();
    }
    
    public boolean hasNext() {
        try {
            if (nextLine == null) {
                reader.close();
            }
            return nextLine != null;
        } catch (Exception e) {
            throw MyException.create(e);
        }
    }

    public Object next() {
        try {
            String ret = nextLine;
            nextLine = reader.readLine();
            return ret;
        } catch (Exception e) {
            throw MyException.create(e);
        }
    }

    public void remove() {            
    }
    
}
