import { useCallback, useEffect, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  Pressable,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { deleteConsumptionRecord, fetchConsumption } from '../api/client';

export default function ConsumptionScreen({ navigation }) {
  const [query, setQuery] = useState('');
  const [year, setYear] = useState(String(new Date().getFullYear()));
  const [records, setRecords] = useState([]);
  const [totalUnits, setTotalUnits] = useState(0);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const load = useCallback(async () => {
    setLoading(true);
    setError('');
    try {
      const data = await fetchConsumption({ q: query, year });
      setRecords(data.records ?? []);
      setTotalUnits(data.totalUnits ?? 0);
    } catch (e) {
      setError(e.message);
    } finally {
      setLoading(false);
    }
  }, [query, year]);

  useEffect(() => {
    load();
  }, [load]);

  return (
    <View style={styles.root}>
      <View style={styles.header}>
        <Text style={styles.title}>Blood consumption</Text>
        <Pressable style={styles.addBtn} onPress={() => navigation.navigate('AddConsumption')}>
          <Text style={styles.addBtnText}>+ Add</Text>
        </Pressable>
      </View>
      <TextInput
        style={styles.input}
        placeholder="Search patient..."
        value={query}
        onChangeText={setQuery}
        onSubmitEditing={load}
      />
      <TextInput style={styles.input} placeholder="Year" value={year} onChangeText={setYear} onSubmitEditing={load} />
      <Pressable style={styles.filterBtn} onPress={load}>
        <Text style={styles.filterBtnText}>Apply filters</Text>
      </Pressable>
      <Text style={styles.summary}>Total units: {totalUnits}</Text>
      {loading ? <ActivityIndicator color="#990000" /> : null}
      {error ? <Text style={styles.error}>{error}</Text> : null}
      <FlatList
        data={records}
        keyExtractor={(item) => String(item.id)}
        renderItem={({ item }) => (
          <View style={styles.card}>
            <Text style={styles.patient}>{item.patient_name}</Text>
            <Text>{item.blood_component} · {item.units} unit(s)</Text>
            <Text style={styles.meta}>{item.date_submitted} · {item.municipality || item.location}</Text>
            <Pressable
              onPress={async () => {
                try {
                  await deleteConsumptionRecord(item.id);
                  await load();
                } catch (e) {
                  setError(e.message);
                }
              }}
            >
              <Text style={styles.delete}>Delete</Text>
            </Pressable>
          </View>
        )}
        ListEmptyComponent={!loading ? <Text style={styles.empty}>No records.</Text> : null}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#fff', padding: 16 },
  header: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 },
  title: { fontSize: 18, fontWeight: '800', color: '#990000' },
  addBtn: { backgroundColor: '#990000', borderRadius: 8, paddingHorizontal: 12, paddingVertical: 8 },
  addBtnText: { color: '#fff', fontWeight: '700' },
  input: { borderWidth: 1, borderColor: '#e2e8f0', borderRadius: 10, padding: 10, marginBottom: 8 },
  filterBtn: { alignSelf: 'flex-start', marginBottom: 8 },
  filterBtnText: { color: '#990000', fontWeight: '700' },
  summary: { fontWeight: '600', marginBottom: 8, color: '#475569' },
  card: { paddingVertical: 12, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
  patient: { fontWeight: '700', fontSize: 16 },
  meta: { color: '#64748b', marginTop: 4 },
  delete: { color: '#dc2626', marginTop: 6, fontWeight: '600' },
  error: { color: '#dc2626' },
  empty: { textAlign: 'center', color: '#94a3b8', marginTop: 24 },
});
