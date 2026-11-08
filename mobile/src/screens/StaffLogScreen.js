import { useCallback, useEffect, useState } from 'react';
import { ActivityIndicator, FlatList, StyleSheet, Text, TextInput, View } from 'react-native';
import { fetchStaffLog } from '../api/client';

export default function StaffLogScreen() {
  const [date, setDate] = useState(new Date().toISOString().slice(0, 10));
  const [entries, setEntries] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const load = useCallback(async () => {
    setLoading(true);
    setError('');
    try {
      const data = await fetchStaffLog(date);
      setEntries(data.entries ?? []);
    } catch (e) {
      setError(e.message);
    } finally {
      setLoading(false);
    }
  }, [date]);

  useEffect(() => {
    load();
  }, [load]);

  return (
    <View style={styles.root}>
      <TextInput style={styles.input} value={date} onChangeText={setDate} onSubmitEditing={load} />
      {loading ? <ActivityIndicator color="#990000" /> : null}
      {error ? <Text style={styles.error}>{error}</Text> : null}
      <FlatList
        data={entries}
        keyExtractor={(item) => String(item.id)}
        renderItem={({ item }) => (
          <View style={styles.row}>
            <Text style={styles.name}>{item.staff}</Text>
            <Text>In: {item.login}</Text>
            <Text>Out: {item.logout ?? '—'}</Text>
          </View>
        )}
        ListEmptyComponent={!loading ? <Text style={styles.empty}>No log entries for this date.</Text> : null}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#fff', padding: 16 },
  input: { borderWidth: 1, borderColor: '#e2e8f0', borderRadius: 10, padding: 10, marginBottom: 12 },
  row: { paddingVertical: 12, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
  name: { fontWeight: '700' },
  error: { color: '#dc2626' },
  empty: { textAlign: 'center', color: '#94a3b8', marginTop: 24 },
});
