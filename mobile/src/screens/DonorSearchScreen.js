import { useCallback, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  Pressable,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { searchDonors } from '../api/client';

export default function DonorSearchScreen({ navigation }) {
  const [query, setQuery] = useState('');
  const [results, setResults] = useState([]);
  const [total, setTotal] = useState(0);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const runSearch = useCallback(async () => {
    setError('');
    setLoading(true);
    try {
      const data = await searchDonors(query);
      setResults(data.donors ?? []);
      setTotal(data.total ?? 0);
    } catch (e) {
      setError(e.message);
      setResults([]);
      setTotal(0);
    } finally {
      setLoading(false);
    }
  }, [query]);

  return (
    <View style={styles.root}>
      <View style={styles.titleRow}>
        <Text style={styles.heading}>Blood collection</Text>
        <Pressable style={styles.registerBtn} onPress={() => navigation.navigate('RegisterDonor')}>
          <Text style={styles.registerBtnText}>+ Register</Text>
        </Pressable>
      </View>
      <Text style={styles.hint}>Search donor by name, barangay, or contact</Text>
      <View style={styles.searchRow}>
        <TextInput
          style={styles.input}
          placeholder="Search donors..."
          value={query}
          onChangeText={setQuery}
          onSubmitEditing={runSearch}
          returnKeyType="search"
        />
        <Pressable style={styles.searchBtn} onPress={runSearch}>
          <Text style={styles.searchBtnText}>Go</Text>
        </Pressable>
      </View>
      {loading ? <ActivityIndicator style={{ marginTop: 16 }} color="#990000" /> : null}
      {error ? <Text style={styles.error}>{error}</Text> : null}
      <Text style={styles.count}>{total} donor{total === 1 ? '' : 's'} found</Text>
      <FlatList
        data={results}
        keyExtractor={(item) => String(item.id)}
        renderItem={({ item }) => (
          <Pressable
            style={styles.row}
            onPress={() => navigation.navigate('DonorDetail', { donorId: item.id, donorName: item.name })}
          >
            <Text style={styles.name}>{item.name}</Text>
          </Pressable>
        )}
        ListEmptyComponent={!loading ? <Text style={styles.empty}>No results yet.</Text> : null}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#fff', padding: 16 },
  titleRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  heading: { fontSize: 20, fontWeight: '800', color: '#990000' },
  registerBtn: { backgroundColor: '#990000', borderRadius: 8, paddingHorizontal: 10, paddingVertical: 6 },
  registerBtnText: { color: '#fff', fontWeight: '700', fontSize: 12 },
  hint: { color: '#64748b', marginBottom: 12 },
  searchRow: { flexDirection: 'row', gap: 8 },
  input: {
    flex: 1,
    borderWidth: 1,
    borderColor: '#e2e8f0',
    borderRadius: 10,
    paddingHorizontal: 12,
    paddingVertical: 10,
  },
  searchBtn: {
    backgroundColor: '#990000',
    borderRadius: 10,
    paddingHorizontal: 16,
    justifyContent: 'center',
  },
  searchBtnText: { color: '#fff', fontWeight: '700' },
  count: { marginTop: 12, marginBottom: 8, color: '#475569', fontWeight: '600' },
  row: {
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: '#f1f5f9',
  },
  name: { fontSize: 16, fontWeight: '600', color: '#1e293b' },
  error: { color: '#dc2626', marginTop: 8 },
  empty: { color: '#94a3b8', marginTop: 24, textAlign: 'center' },
});
