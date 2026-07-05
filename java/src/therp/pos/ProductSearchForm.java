package therp.pos;

import java.awt.BorderLayout;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.util.Iterator;
import java.util.StringTokenizer;

import javax.swing.JButton;
import javax.swing.JFrame;
import javax.swing.JLabel;
import javax.swing.JScrollPane;
import javax.swing.JTable;
import javax.swing.JTextField;
import javax.swing.table.DefaultTableModel;

import therp.util.Request;
import therp.util.TableLayoutPanel;
import therp.util.TableLayoutPanel.Row;

public class ProductSearchForm
    extends JFrame
    implements ActionListener
{
    private JTextField modelField;
    private MyTableModel tableModel;
    
    public ProductSearchForm()
    {
        super("Products");
        getContentPane().setLayout(new BorderLayout());
        TableLayoutPanel searchPanel = new TableLayoutPanel();
        getContentPane().add(searchPanel, BorderLayout.NORTH);
        searchPanel.createColumn(10);
        searchPanel.createColumn();
        searchPanel.createColumn(10);
        searchPanel.createColumn();
        searchPanel.createColumn(10);
        searchPanel.createRow(10);
        Row row = searchPanel.createRow();
        row.createCell();
        row.createCell(new JLabel(tr("Model") + ":"));
        row.createCell();
        modelField = new JTextField(20);
        row.createCell(modelField);
        row = searchPanel.createRow();
        row.createCell();
        JButton button = new JButton(tr("Search"));
        button.setActionCommand("search");
        button.addActionListener(this);
        
        JTable table = new JTable();
        tableModel = new MyTableModel();
        table.setModel(tableModel);
        getContentPane().add(new JScrollPane(table));
    }
    
    
    
    private String tr(String str)
    {
        return str;
    }
    
    public void actionPerformed(ActionEvent event)
    {
        try {
            if ("search".equals(event.getActionCommand())) {
                Request request = createRequest();
                request.setAction("searchproducts");
                request.setParam("model", modelField.getText());
                this.tableModel.init(request.get());
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    
    private class MyTableModel extends DefaultTableModel
    {
        public void init(Iterator i)
        {
            while (getRowCount() > 0)
                removeRow(0);
            while (i.hasNext()) {
                String line = (String) i.next();
                StringTokenizer tokenizer = new StringTokenizer(line, ";");
                Object[] data = new Object[2];
                data[0] = tokenizer.nextToken();
                data[1] = tokenizer.nextToken();
                addRow(data);
            }
        }
    }
    
    private Request createRequest()
    {
        return new Request("sales/posserver.php");
    }
    
}
